<?php
/**
 * Chat API Endpoint
 * Handles AI chat messages and executes SSH operations
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../includes/ai-client.php';
require_once __DIR__ . '/../includes/ssh-manager.php';
require_once __DIR__ . '/../includes/safety-validator.php';
require_once __DIR__ . '/../includes/backup-manager.php';

// Check authentication
session_start();
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $message = trim($_POST['message'] ?? '');
    $sessionId = $_POST['session_id'] ?? null;
    $sessionName = trim($_POST['session_name'] ?? '');
    $websiteId = isset($_POST['website_id']) ? (int)$_POST['website_id'] : null;

    if (empty($message)) {
        throw new Exception('Message is required');
    }

    // Check if customer has active plan
    $stmt = $db->prepare("SELECT * FROM ai_service_plans WHERE customer_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$customerId]);
    $plan = $stmt->fetch();

    if (!$plan) {
        throw new Exception('No active AI plan');
    }

    // Check token limit
    if ($plan['token_limit'] > 0 && $plan['tokens_used'] >= $plan['token_limit']) {
        throw new Exception('Token limit exceeded. Please contact support to upgrade your plan.');
    }

    // Load existing session when provided
    $existingSession = null;
    if ($sessionId) {
        $sessionStmt = $db->prepare("
            SELECT *
            FROM ai_chat_sessions
            WHERE session_id = ? AND customer_id = ?
            LIMIT 1
        ");
        $sessionStmt->execute([$sessionId, $customerId]);
        $existingSession = $sessionStmt->fetch();

        if (!$existingSession) {
            throw new Exception('Invalid session');
        }

        if ((int)$existingSession['is_active'] !== 1) {
            throw new Exception('This chat session has been archived. Create a new session to continue.');
        }

        if (!$websiteId && !empty($existingSession['ssh_credential_id'])) {
            $websiteId = (int)$existingSession['ssh_credential_id'];
        } elseif (!empty($existingSession['ssh_credential_id']) && $websiteId && (int)$existingSession['ssh_credential_id'] !== $websiteId) {
            throw new Exception('Session does not belong to the selected website.');
        }

        if (empty($sessionName)) {
            $sessionName = $existingSession['session_name'] ?? '';
        }
    }

    if (!$websiteId) {
        throw new Exception('Please choose a website to edit before chatting.');
    }

    // Get SSH credentials for selected website
    $stmt = $db->prepare("
        SELECT * FROM customer_ssh_credentials
        WHERE id = ? AND customer_id = ? AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$websiteId, $customerId]);
    $sshCreds = $stmt->fetch();

    if (!$sshCreds) {
        throw new Exception('Invalid or inactive website selection');
    }

    // Create or load session
    if (!$sessionId) {
        $sessionId = uniqid('session_', true);
        $sessionName = $sessionName ?: generateSessionName($message, $sshCreds);

        $stmt = $db->prepare("
            INSERT INTO ai_chat_sessions (customer_id, session_id, ssh_credential_id, session_name, messages)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$customerId, $sessionId, $sshCreds['id'], $sessionName, json_encode([])]);

        $messages = [];
    } else {
        $messages = json_decode($existingSession['messages'], true) ?: [];

        // Backfill missing website linkage on legacy sessions
        if (empty($existingSession['ssh_credential_id'])) {
            $attachStmt = $db->prepare("
                UPDATE ai_chat_sessions
                SET ssh_credential_id = ?
                WHERE session_id = ? AND customer_id = ?
            ");
            $attachStmt->execute([$sshCreds['id'], $sessionId, $customerId]);
        }
    }

    if (!is_string($sessionName)) {
        $sessionName = '';
    }

    // Ensure the session has a friendly name
    if (empty($sessionName) || $sessionName === 'Untitled Chat' || stripos($sessionName, 'New Chat') === 0) {
        $sessionName = generateSessionName($message, $sshCreds);
    }
    $sessionName = mb_substr($sessionName, 0, 255);

    // Add user message to history
    $messages[] = [
        'role' => 'user',
        'content' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Initialize AI client
    $aiClient = new AIClient();

    // Build system prompt with context
    $customerName = $auth->getCurrentCustomerName();
    $context = [
        'customer_name' => $customerName,
        'web_root_path' => $sshCreds['web_root_path'],
        'website_url' => $sshCreds['website_url'],
        'website_type' => $sshCreds['website_type'],
        'website_name' => $sshCreds['website_name'] ?? null
    ];
    $systemPrompt = $aiClient->buildSystemPrompt($context);

    // Get available tools
    $tools = $aiClient->getSSHTools();

    // Send to AI
    $aiResponse = $aiClient->sendMessage($messages, $systemPrompt, $tools);

    if (!$aiResponse['success']) {
        throw new Exception($aiResponse['error'] ?? 'AI request failed');
    }

    $tokensUsed = $aiResponse['tokens_used'];
    $aiContent = $aiResponse['content'];
    $warnings = [];

    // Handle tool calls if any
    if (isset($aiResponse['tool_calls']) && !empty($aiResponse['tool_calls'])) {
        $toolResults = executeToolCalls($aiResponse['tool_calls'], $sshCreds, $customerId, $sessionId);

        // Add tool results to messages and get final response
        foreach ($toolResults as $result) {
            $messages[] = [
                'role' => 'function',
                'content' => json_encode($result),
                'timestamp' => date('Y-m-d H:i:s')
            ];

            if (isset($result['warnings'])) {
                $warnings = array_merge($warnings, $result['warnings']);
            }
        }

        // Get AI's final response after tool execution
        $finalResponse = $aiClient->sendMessage($messages, $systemPrompt);
        if ($finalResponse['success']) {
            $aiContent = $finalResponse['content'];
            $tokensUsed += $finalResponse['tokens_used'];
        }
    }

    // Add AI response to messages
    $messages[] = [
        'role' => 'assistant',
        'content' => $aiContent,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Update session
    $stmt = $db->prepare("
        UPDATE ai_chat_sessions
        SET messages = ?, total_tokens_used = total_tokens_used + ?, last_message_at = NOW(), session_name = ?, ssh_credential_id = ?
        WHERE session_id = ?
    ");
    $stmt->execute([json_encode($messages), $tokensUsed, $sessionName, $sshCreds['id'], $sessionId]);

    // Update plan token usage
    $stmt = $db->prepare("UPDATE ai_service_plans SET tokens_used = tokens_used + ? WHERE id = ?");
    $stmt->execute([$tokensUsed, $plan['id']]);

    // Log the interaction
    $stmt = $db->prepare("
        INSERT INTO ai_change_logs
        (customer_id, session_id, ssh_credential_id, user_request, ai_response, tokens_used, success)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
        $customerId,
        $sessionId,
        $sshCreds['id'],
        $message,
        $aiContent,
        $tokensUsed
    ]);

    // Calculate remaining tokens
    $tokensRemaining = $plan['token_limit'] > 0 ? $plan['token_limit'] - ($plan['tokens_used'] + $tokensUsed) : -1;

    // Return response
    echo json_encode([
        'success' => true,
        'response' => $aiContent,
        'session_id' => $sessionId,
        'session_name' => $sessionName,
        'website_id' => (int)$sshCreds['id'],
        'tokens_used' => $tokensUsed,
        'tokens_remaining' => $tokensRemaining,
        'warnings' => $warnings
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Build a short, user-friendly session name
 */
function generateSessionName($message, $sshCreds = null) {
    $prefix = '';

    if (is_array($sshCreds)) {
        $prefix = $sshCreds['website_name'] ?? ($sshCreds['website_url'] ?? '');
        if (!empty($prefix)) {
            $prefix = trim($prefix) . ' - ';
        }
    }

    $snippet = preg_replace('/\s+/', ' ', trim($message));
    $snippet = mb_substr($snippet, 0, 60);

    if (mb_strlen($snippet) === 0) {
        $snippet = 'Chat Session';
    }

    return $prefix . $snippet;
}

/**
 * Execute tool calls (SSH operations)
 */
function executeToolCalls($toolCalls, $sshCreds, $customerId, $sessionId) {
    $results = [];
    $validator = new SafetyValidator();
    $sshManager = new SSHManager();
    $backupManager = new BackupManager();

    // Connect to SSH
    try {
        $sshManager->connect($sshCreds);
    } catch (Exception $e) {
        return [[
            'success' => false,
            'error' => 'SSH connection failed: ' . $e->getMessage()
        ]];
    }

    foreach ($toolCalls as $toolCall) {
        $functionName = $toolCall['function']['name'] ?? '';
        $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true);

        try {
            switch ($functionName) {
                case 'read_file':
                    $filepath = $arguments['filepath'] ?? '';

                    // Validate path
                    $pathValidation = $validator->validatePath($filepath, $sshCreds['web_root_path']);
                    if (!$pathValidation['valid']) {
                        throw new Exception($pathValidation['reason']);
                    }

                    $content = $sshManager->readFile($filepath);
                    $results[] = [
                        'tool' => 'read_file',
                        'success' => true,
                        'filepath' => $filepath,
                        'content' => $content
                    ];
                    break;

                case 'write_file':
                    $filepath = $arguments['filepath'] ?? '';
                    $content = $arguments['content'] ?? '';

                    // Validate path
                    $pathValidation = $validator->validatePath($filepath, $sshCreds['web_root_path']);
                    if (!$pathValidation['valid']) {
                        throw new Exception($pathValidation['reason']);
                    }

                    // Validate content
                    $contentValidation = $validator->validateFileContent($content);
                    $warnings = $contentValidation['warnings'] ?? [];

                    // Create backup if file exists
                    $backupPath = null;
                    if ($sshManager->fileExists($filepath)) {
                        $backupPath = $sshManager->createBackup($filepath);
                    }

                    // Write file
                    $sshManager->writeFile($filepath, $content);

                    $results[] = [
                        'tool' => 'write_file',
                        'success' => true,
                        'filepath' => $filepath,
                        'backup_path' => $backupPath,
                        'warnings' => $warnings
                    ];
                    break;

                case 'list_directory':
                    $directory = $arguments['directory'] ?? '';

                    // Validate path
                    $pathValidation = $validator->validatePath($directory, $sshCreds['web_root_path']);
                    if (!$pathValidation['valid']) {
                        throw new Exception($pathValidation['reason']);
                    }

                    $listing = $sshManager->listDirectory($directory);
                    $results[] = [
                        'tool' => 'list_directory',
                        'success' => true,
                        'directory' => $directory,
                        'listing' => $listing
                    ];
                    break;

                case 'execute_command':
                    $command = $arguments['command'] ?? '';

                    // Validate command
                    $commandValidation = $validator->validateCommand($command);
                    if (!$commandValidation['valid']) {
                        throw new Exception($commandValidation['reason']);
                    }

                    $result = $sshManager->executeCommand($command);
                    $results[] = [
                        'tool' => 'execute_command',
                        'success' => $result['success'],
                        'command' => $command,
                        'output' => $result['output'],
                        'error' => $result['error']
                    ];
                    break;

                case 'backup_file':
                    $filepath = $arguments['filepath'] ?? '';

                    // Validate path
                    $pathValidation = $validator->validatePath($filepath, $sshCreds['web_root_path']);
                    if (!$pathValidation['valid']) {
                        throw new Exception($pathValidation['reason']);
                    }

                    $backupPath = $sshManager->createBackup($filepath);
                    $results[] = [
                        'tool' => 'backup_file',
                        'success' => true,
                        'filepath' => $filepath,
                        'backup_path' => $backupPath
                    ];
                    break;

                case 'file_exists':
                    $filepath = $arguments['filepath'] ?? '';

                    // Validate path
                    $pathValidation = $validator->validatePath($filepath, $sshCreds['web_root_path']);
                    if (!$pathValidation['valid']) {
                        throw new Exception($pathValidation['reason']);
                    }

                    $exists = $sshManager->fileExists($filepath);
                    $results[] = [
                        'tool' => 'file_exists',
                        'success' => true,
                        'filepath' => $filepath,
                        'exists' => $exists
                    ];
                    break;

                default:
                    throw new Exception('Unknown tool: ' . $functionName);
            }

        } catch (Exception $e) {
            $results[] = [
                'tool' => $functionName,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    return $results;
}
?>
