<?php
/**
 * AI Client
 * Handles communication with OpenAI-compatible API
 * Supports function calling for SSH operations
 */

require_once __DIR__ . '/ai-anthropic-adapter.php';

class AIClient {
    private $api_endpoint;
    private $api_key;
    private $model;
    private $db;

    public function __construct() {
        // Reuse existing DB class if already loaded (chat.php includes the shared Database)
        if (!class_exists('Database')) {
            $sharedDb = __DIR__ . '/../../includes/database.php';
            $panelDb = __DIR__ . '/../../panel/includes/database.php';

            if (file_exists($sharedDb)) {
                require_once $sharedDb;
            } elseif (file_exists($panelDb)) {
                require_once $panelDb;
            } else {
                throw new Exception('Database configuration not found');
            }
        }

        $this->db = Database::getInstance()->getConnection();

        // Load configuration from system settings
        $this->loadConfiguration();
    }

    /**
     * Load AI configuration from database
     */
    private function loadConfiguration() {
        $settings = $this->getSettings(['ai_openai_endpoint', 'ai_openai_key', 'ai_model_name']);

        $this->api_endpoint = $settings['ai_openai_endpoint'] ?? '';
        $this->api_key = $settings['ai_openai_key'] ?? '';
        $this->model = $settings['ai_model_name'] ?? 'gpt-4';

        if (empty($this->api_endpoint) || empty($this->api_key)) {
            throw new Exception('AI API not configured. Please configure endpoint and API key in system settings.');
        }
    }

    /**
     * Get settings from database
     */
    private function getSettings($keys) {
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $this->db->prepare("SELECT `key`, `value` FROM system_settings WHERE `key` IN ($placeholders)");
        $stmt->execute($keys);

        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }

    /**
     * Send a message to the AI and get response
     * @param array $messages Conversation history
     * @param string $systemPrompt System prompt with context
     * @param array $tools Available function tools
     * @return array AI response
     */
    public function sendMessage($messages, $systemPrompt = '', $tools = []) {
        try {
            // Convert messages to Anthropic format
            $converted = AnthropicAdapter::convertMessages($messages, $systemPrompt);

            // Build request payload for Anthropic API
            $payload = [
                'model' => $this->model,
                'messages' => $converted['messages'],
                'max_tokens' => 2000
            ];

            // Add system prompt if provided
            if (!empty($converted['system'])) {
                $payload['system'] = $converted['system'];
            }

            // Make API request
            $response = $this->makeRequest($payload);

            // Convert response back to OpenAI format
            return AnthropicAdapter::convertResponse($response);

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tokens_used' => 0
            ];
        }
    }

    /**
     * Build messages array with system prompt
     */
    private function buildMessages($messages, $systemPrompt) {
        $formatted = [];

        // Add system prompt first
        if (!empty($systemPrompt)) {
            $formatted[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }

        // Add conversation messages
        foreach ($messages as $msg) {
            $formatted[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content']
            ];
        }

        return $formatted;
    }

    /**
     * Make HTTP request to AI API
     */
    private function makeRequest($payload) {
        $ch = curl_init($this->api_endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->api_key
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new Exception("API request failed: {$curl_error}");
        }

        if ($http_code !== 200) {
            throw new Exception("API returned error code: {$http_code}");
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse API response: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Parse AI response
     */
    private function parseResponse($response) {
        if (!isset($response['choices'][0])) {
            throw new Exception("Invalid API response format");
        }

        $choice = $response['choices'][0];
        $message = $choice['message'] ?? [];

        $result = [
            'success' => true,
            'content' => $message['content'] ?? '',
            'role' => $message['role'] ?? 'assistant',
            'finish_reason' => $choice['finish_reason'] ?? 'stop',
            'tokens_used' => $response['usage']['total_tokens'] ?? 0
        ];

        // Check for function calls
        if (isset($message['tool_calls']) && !empty($message['tool_calls'])) {
            $result['tool_calls'] = $message['tool_calls'];
        }

        return $result;
    }

    /**
     * Get available tools/functions for SSH operations
     */
    public function getSSHTools() {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'read_file',
                    'description' => 'Read the contents of a file from the website server',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'filepath' => [
                                'type' => 'string',
                                'description' => 'Absolute path to the file to read'
                            ]
                        ],
                        'required' => ['filepath']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'write_file',
                    'description' => 'Write or update a file on the website server. Always create a backup before using this.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'filepath' => [
                                'type' => 'string',
                                'description' => 'Absolute path to the file to write'
                            ],
                            'content' => [
                                'type' => 'string',
                                'description' => 'Content to write to the file'
                            ]
                        ],
                        'required' => ['filepath', 'content']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_directory',
                    'description' => 'List files and directories in a specific path',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'directory' => [
                                'type' => 'string',
                                'description' => 'Directory path to list'
                            ]
                        ],
                        'required' => ['directory']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'execute_command',
                    'description' => 'Execute a safe shell command on the server. Only approved commands are allowed.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'command' => [
                                'type' => 'string',
                                'description' => 'Shell command to execute (must be from allowed list)'
                            ]
                        ],
                        'required' => ['command']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'backup_file',
                    'description' => 'Create a backup of a file before making changes',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'filepath' => [
                                'type' => 'string',
                                'description' => 'Path to file to backup'
                            ]
                        ],
                        'required' => ['filepath']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'file_exists',
                    'description' => 'Check if a file exists on the server',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'filepath' => [
                                'type' => 'string',
                                'description' => 'Path to file to check'
                            ]
                        ],
                        'required' => ['filepath']
                    ]
                ]
            ]
        ];
    }

    /**
     * Build system prompt for AI website editor
     */
    public function buildSystemPrompt($context) {
        $webRoot = $context['web_root_path'] ?? '/var/www/html';
        $websiteType = $context['website_type'] ?? 'custom';
        $websiteUrl = $context['website_url'] ?? 'N/A';
        $customerName = $context['customer_name'] ?? 'Customer';

        return <<<PROMPT
You are an AI website editor with SSH access to the customer's web server.

CUSTOMER CONTEXT:
- Name: {$customerName}
- Website: {$websiteUrl}
- Web Root: {$webRoot}
- Website Type: {$websiteType}

YOUR CAPABILITIES:
You have access to the following tools to manage the website:
- read_file: Read file contents from the server
- write_file: Edit or create files (HTML, CSS, JavaScript, PHP, etc.)
- list_directory: View directory contents
- execute_command: Run safe shell commands (limited list)
- backup_file: Create file backups before changes
- file_exists: Check if a file exists

SAFETY RULES (CRITICAL - YOU MUST FOLLOW THESE):
1. ALWAYS create a backup before modifying any file (use backup_file tool)
2. ALWAYS explain what you're about to do before doing it
3. NEVER delete databases or execute DROP/DELETE commands
4. NEVER modify files outside the web root path: {$webRoot}
5. NEVER execute dangerous commands (rm -rf, chmod 777, dd, mkfs, etc.)
6. ASK for confirmation before any destructive operations
7. VALIDATE all file paths to ensure they're within the web root
8. Be cautious with file permissions and ownership

WORKFLOW:
1. User describes the change they want
2. You locate the relevant files using list_directory or file_exists
3. You explain the changes you'll make and ask for confirmation if needed
4. You create backups using backup_file
5. You make the changes using write_file
6. You verify the changes were successful
7. You report success with the file paths you modified

RESPONSE FORMAT:
- Be conversational, friendly, and helpful
- Show file paths clearly using code blocks when relevant
- Explain technical concepts in simple terms
- If you're unsure about something, ask clarifying questions
- Always confirm successful operations
- If something fails, explain what went wrong

EXAMPLE INTERACTION:
User: "Change the homepage header to say 'Welcome to My Site'"
You: "I'll help you change the homepage header. Let me first check your website structure..."
[Use list_directory to find index.html or index.php]
You: "I found your homepage at /var/www/html/index.html. I'll now:"
1. Create a backup of the file
2. Update the header text to 'Welcome to My Site'
3. Save the changes

[Use backup_file, then read_file, modify content, write_file]
You: "Done! I've successfully updated your homepage header. The old version is backed up at /var/www/html/.ai_backups/index.html.backup.2025-11-18_10-30-00"

Remember: Safety first! Always backup before changes, validate paths, and explain your actions.
PROMPT;
    }

    /**
     * Estimate token count (rough estimate)
     */
    public function estimateTokens($text) {
        // Rough estimate: ~4 characters per token
        return ceil(strlen($text) / 4);
    }
}
?>
