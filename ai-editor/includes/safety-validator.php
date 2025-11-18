<?php
/**
 * Safety Validator
 * Validates commands, paths, and operations before execution
 * Prevents dangerous operations and path traversal attacks
 */

class SafetyValidator {
    private $db;
    private $allowedCommands = [];
    private $blockedCommands = [];
    private $maxFileSizeMB = 5;

    public function __construct() {
        require_once __DIR__ . '/../../includes/database.php';
        $this->db = Database::getInstance()->getConnection();
        $this->loadSafetySettings();
    }

    /**
     * Load safety settings from database
     */
    private function loadSafetySettings() {
        $stmt = $this->db->prepare("
            SELECT `key`, `value`
            FROM system_settings
            WHERE `key` IN ('ai_allowed_commands', 'ai_blocked_commands', 'ai_max_file_size_mb')
        ");
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            switch ($row['key']) {
                case 'ai_allowed_commands':
                    $this->allowedCommands = json_decode($row['value'], true) ?: $this->getDefaultAllowedCommands();
                    break;
                case 'ai_blocked_commands':
                    $this->blockedCommands = json_decode($row['value'], true) ?: $this->getDefaultBlockedCommands();
                    break;
                case 'ai_max_file_size_mb':
                    $this->maxFileSizeMB = intval($row['value']) ?: 5;
                    break;
            }
        }

        // If not configured, use defaults
        if (empty($this->allowedCommands)) {
            $this->allowedCommands = $this->getDefaultAllowedCommands();
        }
        if (empty($this->blockedCommands)) {
            $this->blockedCommands = $this->getDefaultBlockedCommands();
        }
    }

    /**
     * Default allowed commands
     */
    private function getDefaultAllowedCommands() {
        return [
            'ls', 'cat', 'grep', 'find', 'head', 'tail', 'wc', 'pwd',
            'whoami', 'stat', 'file', 'du', 'df', 'test', 'echo',
            'cp', 'mkdir', 'touch'
        ];
    }

    /**
     * Default blocked commands (high-risk)
     */
    private function getDefaultBlockedCommands() {
        return [
            'rm', 'rmdir', 'dd', 'mkfs', 'fdisk', 'parted',
            'chmod 777', 'chown', 'kill', 'killall', 'pkill',
            'shutdown', 'reboot', 'init', 'systemctl',
            'service', 'iptables', 'ufw', 'firewall-cmd',
            'useradd', 'usermod', 'userdel', 'passwd',
            'su', 'sudo', 'visudo',
            'mysql', 'psql', 'mongo', 'redis-cli',
            'DROP', 'DELETE', 'TRUNCATE', 'ALTER TABLE',
            'wget', 'curl', 'nc', 'netcat', 'telnet',
            'fork bomb', ':(){:|:&};:',
            'eval', 'exec', 'system'
        ];
    }

    /**
     * Validate a shell command
     * @param string $command Command to validate
     * @return array ['valid' => bool, 'reason' => string]
     */
    public function validateCommand($command) {
        $command = trim($command);

        // Check if command is empty
        if (empty($command)) {
            return ['valid' => false, 'reason' => 'Empty command'];
        }

        // Extract the base command (first word)
        $parts = explode(' ', $command);
        $baseCommand = $parts[0];

        // Check against blocked commands
        foreach ($this->blockedCommands as $blocked) {
            if (stripos($command, $blocked) !== false) {
                return [
                    'valid' => false,
                    'reason' => "Blocked command detected: {$blocked}"
                ];
            }
        }

        // Check if base command is in allowed list
        if (!in_array($baseCommand, $this->allowedCommands)) {
            return [
                'valid' => false,
                'reason' => "Command not in allowed list: {$baseCommand}. Allowed: " . implode(', ', $this->allowedCommands)
            ];
        }

        // Check for command chaining attempts (;, &, |, &&, ||)
        if (preg_match('/[;&|]/', $command)) {
            return [
                'valid' => false,
                'reason' => 'Command chaining not allowed (;, &, |)'
            ];
        }

        // Check for command substitution ($(), backticks)
        if (preg_match('/\$\(|\`/', $command)) {
            return [
                'valid' => false,
                'reason' => 'Command substitution not allowed ($(), backticks)'
            ];
        }

        // Check for newlines (could enable command injection)
        if (preg_match('/[\r\n]/', $command)) {
            return [
                'valid' => false,
                'reason' => 'Newlines not allowed in commands'
            ];
        }

        // Check for redirections
        if (preg_match('/[<>]/', $command)) {
            return [
                'valid' => false,
                'reason' => 'Command redirection not allowed'
            ];
        }

        // Check for multiple spaces (could hide malicious commands)
        if (preg_match('/\s{2,}/', $command)) {
            return [
                'valid' => false,
                'reason' => 'Multiple consecutive spaces not allowed'
            ];
        }

        return ['valid' => true, 'reason' => ''];
    }

    /**
     * Validate a file path
     * @param string $path Path to validate
     * @param string $webRoot Web root directory
     * @return array ['valid' => bool, 'reason' => string]
     */
    public function validatePath($path, $webRoot) {
        // Normalize paths
        $webRoot = rtrim($webRoot, '/');
        $path = trim($path);

        // Check for empty path
        if (empty($path)) {
            return ['valid' => false, 'reason' => 'Empty path'];
        }

        // Check for null bytes
        if (strpos($path, "\0") !== false) {
            return ['valid' => false, 'reason' => 'Null byte detected in path'];
        }

        // Check for directory traversal attempts
        if (strpos($path, '..') !== false) {
            return ['valid' => false, 'reason' => 'Directory traversal detected (..)'];
        }

        // Check for URL-encoded traversal attempts
        if (preg_match('/%2e%2e|%252e%252e|\.\.%2f|\.\.%5c/i', $path)) {
            return ['valid' => false, 'reason' => 'URL-encoded path traversal detected'];
        }

        // Normalize path: remove duplicate slashes
        $path = preg_replace('#/+#', '/', $path);
        $path = rtrim($path, '/');

        // Convert to absolute path if relative
        if ($path[0] !== '/') {
            $path = $webRoot . '/' . ltrim($path, '/');
        }

        // Ensure path is within web root
        if (strpos($path, $webRoot) !== 0) {
            return [
                'valid' => false,
                'reason' => "Path is outside web root. Path: {$path}, Web Root: {$webRoot}"
            ];
        }

        // Additional check: ensure no path component is '..' or '.'
        $pathParts = explode('/', $path);
        foreach ($pathParts as $part) {
            if ($part === '..' || $part === '.') {
                return ['valid' => false, 'reason' => 'Invalid path component detected'];
            }
        }

        // Check for sensitive files
        $sensitivePatterns = [
            '/etc/passwd',
            '/etc/shadow',
            '/.env',
            '/config.php',
            '/.git/',
            '/wp-config.php',
            '/.htaccess'
        ];

        foreach ($sensitivePatterns as $pattern) {
            if (stripos($path, $pattern) !== false) {
                // Allow if explicitly within web root, but warn
                if (strpos($path, $webRoot) === 0) {
                    // Allow but could add logging here
                    continue;
                }
                return [
                    'valid' => false,
                    'reason' => "Sensitive file access denied: {$pattern}"
                ];
            }
        }

        return ['valid' => true, 'reason' => ''];
    }

    /**
     * Validate file size
     * @param int $sizeInBytes File size in bytes
     * @return array ['valid' => bool, 'reason' => string]
     */
    public function validateFileSize($sizeInBytes) {
        $maxBytes = $this->maxFileSizeMB * 1024 * 1024;

        if ($sizeInBytes > $maxBytes) {
            return [
                'valid' => false,
                'reason' => "File size ({$sizeInBytes} bytes) exceeds maximum allowed ({$this->maxFileSizeMB} MB)"
            ];
        }

        return ['valid' => true, 'reason' => ''];
    }

    /**
     * Validate file content
     * @param string $content File content
     * @return array ['valid' => bool, 'reason' => string, 'warnings' => array]
     */
    public function validateFileContent($content) {
        $warnings = [];

        // Check for potentially dangerous PHP functions
        $dangerousFunctions = [
            'eval(', 'exec(', 'system(', 'passthru(', 'shell_exec(',
            'popen(', 'proc_open(', 'pcntl_exec(',
            'base64_decode(', 'assert(', 'create_function('
        ];

        foreach ($dangerousFunctions as $func) {
            if (stripos($content, $func) !== false) {
                $warnings[] = "Potentially dangerous PHP function detected: {$func}";
            }
        }

        // Check for SQL injection patterns
        $sqlPatterns = [
            '/DROP\s+TABLE/i',
            '/DELETE\s+FROM/i',
            '/TRUNCATE\s+TABLE/i',
            '/UPDATE\s+.*\s+SET/i'
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $warnings[] = "Potentially dangerous SQL pattern detected";
                break;
            }
        }

        // Check for XSS patterns
        if (preg_match('/<script\b[^>]*>/i', $content)) {
            $warnings[] = "Script tag detected - ensure proper sanitization";
        }

        return [
            'valid' => true,  // We allow but warn
            'reason' => '',
            'warnings' => $warnings
        ];
    }

    /**
     * Sanitize user input
     * @param string $input User input
     * @return string Sanitized input
     */
    public function sanitizeInput($input) {
        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Trim whitespace
        $input = trim($input);

        return $input;
    }

    /**
     * Escape shell argument
     * @param string $arg Argument to escape
     * @return string Escaped argument
     */
    public function escapeShellArg($arg) {
        return escapeshellarg($arg);
    }

    /**
     * Check if operation is allowed
     * @param string $operation Operation type (read, write, execute, delete)
     * @param string $path File path
     * @param string $webRoot Web root
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function isOperationAllowed($operation, $path, $webRoot) {
        // Validate path first
        $pathValidation = $this->validatePath($path, $webRoot);
        if (!$pathValidation['valid']) {
            return ['allowed' => false, 'reason' => $pathValidation['reason']];
        }

        // Check operation-specific rules
        switch ($operation) {
            case 'delete':
                return [
                    'allowed' => false,
                    'reason' => 'File deletion is not allowed through AI editor'
                ];

            case 'write':
                // Check if it's a critical system file
                $criticalFiles = ['.htaccess', 'wp-config.php', '.env'];
                $basename = basename($path);

                if (in_array($basename, $criticalFiles)) {
                    return [
                        'allowed' => true,  // Allow but should require extra confirmation
                        'reason' => '',
                        'warning' => "You're modifying a critical file: {$basename}. Please ensure you have a backup."
                    ];
                }
                break;

            case 'read':
            case 'execute':
                // Generally allowed if path is valid
                break;
        }

        return ['allowed' => true, 'reason' => ''];
    }

    /**
     * Log security event
     * @param string $eventType Type of event
     * @param string $details Event details
     * @param int $customerId Customer ID
     */
    public function logSecurityEvent($eventType, $details, $customerId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_change_logs
                (customer_id, user_request, ai_response, success, error_message, executed_at)
                VALUES (?, ?, ?, 0, ?, NOW())
            ");

            $stmt->execute([
                $customerId,
                "SECURITY_EVENT: {$eventType}",
                '',
                $details
            ]);
        } catch (Exception $e) {
            // Silent fail - don't break operation if logging fails
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
}
?>
