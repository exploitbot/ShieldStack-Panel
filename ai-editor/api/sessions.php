<?php
/**
 * Chat Session Management
 * - List sessions for a website
 * - Create new sessions
 * - Clear/reset existing sessions
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/database.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $websiteId = isset($_REQUEST['website_id']) ? (int)$_REQUEST['website_id'] : null;

    if (!$websiteId) {
        throw new Exception('Website selection is required.');
    }

    $website = getWebsite($db, $customerId, $websiteId);
    if (!$website) {
        throw new Exception('Website not found or inactive.');
    }

    switch ($method) {
        case 'GET':
            listSessions($db, $customerId, $websiteId);
            break;
        case 'POST':
            handlePost($db, $customerId, $websiteId);
            break;
        default:
            throw new Exception('Unsupported request method.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Fetch website for the current customer
 */
function getWebsite($db, $customerId, $websiteId) {
    $stmt = $db->prepare("
        SELECT *
        FROM customer_ssh_credentials
        WHERE id = ? AND customer_id = ? AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$websiteId, $customerId]);
    return $stmt->fetch();
}

/**
 * List active chat sessions for a website
 */
function listSessions($db, $customerId, $websiteId) {
    $stmt = $db->prepare("
        SELECT session_id, session_name, messages, total_tokens_used, created_at, last_message_at
        FROM ai_chat_sessions
        WHERE customer_id = ? AND ssh_credential_id = ? AND is_active = 1
        ORDER BY COALESCE(last_message_at, created_at) DESC, id DESC
    ");
    $stmt->execute([$customerId, $websiteId]);

    $sessions = [];
    while ($row = $stmt->fetch()) {
        $messages = json_decode($row['messages'], true) ?: [];
        $preview = getPreview($messages);

        $sessions[] = [
            'session_id' => $row['session_id'],
            'session_name' => $row['session_name'] ?? 'Untitled Chat',
            'created_at' => $row['created_at'],
            'last_message_at' => $row['last_message_at'],
            'total_tokens_used' => (int)$row['total_tokens_used'],
            'last_message_preview' => $preview['text'],
            'last_message_role' => $preview['role']
        ];
    }

    echo json_encode([
        'success' => true,
        'sessions' => $sessions
    ]);
    exit;
}

/**
 * Handle POST actions (create, clear)
 */
function handlePost($db, $customerId, $websiteId) {
    $action = $_POST['action'] ?? 'create';

    switch ($action) {
        case 'create':
            $sessionName = trim($_POST['session_name'] ?? '') ?: defaultSessionName();
            $sessionId = uniqid('session_', true);

            $stmt = $db->prepare("
                INSERT INTO ai_chat_sessions (customer_id, session_id, ssh_credential_id, session_name, messages)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customerId,
                $sessionId,
                $websiteId,
                $sessionName,
                json_encode([])
            ]);

            echo json_encode([
                'success' => true,
                'session' => [
                    'session_id' => $sessionId,
                    'session_name' => $sessionName,
                    'created_at' => date('Y-m-d H:i:s'),
                    'last_message_at' => null,
                    'total_tokens_used' => 0,
                    'last_message_preview' => ''
                ]
            ]);
            exit;

        case 'clear':
            $sessionId = $_POST['session_id'] ?? '';
            if (empty($sessionId)) {
                throw new Exception('Session ID is required to clear a chat.');
            }

            $session = fetchSession($db, $customerId, $websiteId, $sessionId);
            if (!$session) {
                throw new Exception('Session not found for this website.');
            }

            $stmt = $db->prepare("
                UPDATE ai_chat_sessions
                SET messages = ?, total_tokens_used = 0, last_message_at = NULL, updated_at = NOW()
                WHERE session_id = ? AND customer_id = ? AND ssh_credential_id = ?
            ");
            $stmt->execute([json_encode([]), $sessionId, $customerId, $websiteId]);

            echo json_encode([
                'success' => true,
                'session_id' => $sessionId
            ]);
            exit;

        default:
            throw new Exception('Unsupported action.');
    }
}

/**
 * Get a session by id/website
 */
function fetchSession($db, $customerId, $websiteId, $sessionId) {
    $stmt = $db->prepare("
        SELECT * FROM ai_chat_sessions
        WHERE session_id = ? AND customer_id = ? AND ssh_credential_id = ? AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$sessionId, $customerId, $websiteId]);
    return $stmt->fetch();
}

/**
 * Build preview text for session list
 */
function getPreview($messages) {
    if (empty($messages)) {
        return ['text' => '', 'role' => null];
    }

    $last = end($messages);
    $text = $last['content'] ?? '';
    $preview = mb_substr($text, 0, 140);

    if (mb_strlen($text) > 140) {
        $preview .= '...';
    }

    return [
        'text' => $preview,
        'role' => $last['role'] ?? 'assistant'
    ];
}

/**
 * Generate a default session name
 */
function defaultSessionName() {
    return 'New Chat ' . date('M j, g:i A');
}
