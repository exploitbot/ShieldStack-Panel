<?php
/**
 * SSH Manager
 * Handles SSH connections and remote file operations
 * Requires PHP ssh2 extension (pecl install ssh2)
 */

require_once __DIR__ . '/encryption.php';

class SSHManager {
    private $connection = null;
    private $sftp = null;
    private $host;
    private $port;
    private $username;
    private $web_root;
    private $connected = false;

    /**
     * Connect to SSH server
     * @param array $credentials SSH credentials from database
     * @return bool Connection success
     */
    public function connect($credentials) {
        try {
            // Check if ssh2 extension is available
            if (!function_exists('ssh2_connect')) {
                throw new Exception('PHP ssh2 extension not installed. Install with: pecl install ssh2');
            }

            $this->host = $credentials['ssh_host'];
            $this->port = $credentials['ssh_port'] ?? 22;
            $this->username = $credentials['ssh_username'];
            $this->web_root = rtrim($credentials['web_root_path'], '/');

            // Attempt connection
            $this->connection = ssh2_connect($this->host, $this->port);

            if (!$this->connection) {
                throw new Exception("Failed to connect to SSH server: {$this->host}:{$this->port}");
            }

            // Decrypt password
            $encryption = new CredentialEncryption();

            // Try password authentication first
            if (!empty($credentials['ssh_password_encrypted'])) {
                $password = $encryption->decrypt($credentials['ssh_password_encrypted']);

                if (!ssh2_auth_password($this->connection, $this->username, $password)) {
                    throw new Exception("SSH authentication failed for user: {$this->username}");
                }
            }
            // Try key-based authentication
            elseif (!empty($credentials['ssh_key_encrypted'])) {
                $privateKey = $encryption->decrypt($credentials['ssh_key_encrypted']);

                // Save key to temporary file
                $keyFile = tempnam(sys_get_temp_dir(), 'ssh_key_');
                file_put_contents($keyFile, $privateKey);
                chmod($keyFile, 0600);

                try {
                    if (!ssh2_auth_pubkey_file($this->connection, $this->username, null, $keyFile)) {
                        throw new Exception("SSH key authentication failed");
                    }
                } finally {
                    unlink($keyFile);
                }
            } else {
                throw new Exception("No SSH credentials configured");
            }

            // Initialize SFTP subsystem
            $this->sftp = ssh2_sftp($this->connection);
            if (!$this->sftp) {
                throw new Exception("Failed to initialize SFTP subsystem");
            }

            $this->connected = true;
            return true;

        } catch (Exception $e) {
            $this->connected = false;
            throw $e;
        }
    }

    /**
     * Execute a command on the remote server
     * @param string $command Command to execute
     * @return array ['output' => string, 'exit_code' => int]
     */
    public function executeCommand($command) {
        if (!$this->connected) {
            throw new Exception("Not connected to SSH server");
        }

        $stream = ssh2_exec($this->connection, $command);

        if (!$stream) {
            throw new Exception("Failed to execute command: {$command}");
        }

        stream_set_blocking($stream, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $stream_err = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        $output = stream_get_contents($stream_out);
        $error = stream_get_contents($stream_err);

        fclose($stream_out);
        fclose($stream_err);
        fclose($stream);

        return [
            'output' => $output,
            'error' => $error,
            'success' => empty($error)
        ];
    }

    /**
     * Read file contents from remote server
     * @param string $filepath Absolute path to file
     * @return string File contents
     */
    public function readFile($filepath) {
        if (!$this->connected) {
            throw new Exception("Not connected to SSH server");
        }

        // Validate path is within web root
        if (!$this->isPathSafe($filepath)) {
            throw new Exception("Path is outside web root: {$filepath}");
        }

        $sftp_path = "ssh2.sftp://" . intval($this->sftp) . $filepath;
        $contents = file_get_contents($sftp_path);

        if ($contents === false) {
            throw new Exception("Failed to read file: {$filepath}");
        }

        return $contents;
    }

    /**
     * Write content to a file on remote server
     * @param string $filepath Absolute path to file
     * @param string $content Content to write
     * @return bool Success
     */
    public function writeFile($filepath, $content) {
        if (!$this->connected) {
            throw new Exception("Not connected to SSH server");
        }

        // Validate path is within web root
        if (!$this->isPathSafe($filepath)) {
            throw new Exception("Path is outside web root: {$filepath}");
        }

        $sftp_path = "ssh2.sftp://" . intval($this->sftp) . $filepath;
        $result = file_put_contents($sftp_path, $content);

        if ($result === false) {
            throw new Exception("Failed to write file: {$filepath}");
        }

        return true;
    }

    /**
     * List files in a directory
     * @param string $directory Directory path
     * @return array List of files and directories
     */
    public function listDirectory($directory) {
        if (!$this->connected) {
            throw new Exception("Not connected to SSH server");
        }

        // Validate path is within web root
        if (!$this->isPathSafe($directory)) {
            throw new Exception("Path is outside web root: {$directory}");
        }

        $result = $this->executeCommand("ls -la " . escapeshellarg($directory));

        if (!$result['success']) {
            throw new Exception("Failed to list directory: {$directory}");
        }

        return $result['output'];
    }

    /**
     * Create a backup of a file
     * @param string $filepath File to backup
     * @return string Backup file path
     */
    public function createBackup($filepath) {
        if (!$this->connected) {
            throw new Exception("Not connected to SSH server");
        }

        if (!$this->isPathSafe($filepath)) {
            throw new Exception("Path is outside web root: {$filepath}");
        }

        // Create backup directory if it doesn't exist
        $backupDir = $this->web_root . '/.ai_backups';
        $this->executeCommand("mkdir -p " . escapeshellarg($backupDir));

        // Generate backup filename with timestamp
        $timestamp = date('Y-m-d_H-i-s');
        $basename = basename($filepath);
        $backupPath = $backupDir . '/' . $basename . '.backup.' . $timestamp;

        // Copy file to backup location
        $result = $this->executeCommand("cp " . escapeshellarg($filepath) . " " . escapeshellarg($backupPath));

        if (!$result['success']) {
            throw new Exception("Failed to create backup: {$filepath}");
        }

        return $backupPath;
    }

    /**
     * Check if file exists
     * @param string $filepath File path
     * @return bool File exists
     */
    public function fileExists($filepath) {
        if (!$this->connected) {
            throw new Exception("Not connected to SSH server");
        }

        if (!$this->isPathSafe($filepath)) {
            return false;
        }

        $result = $this->executeCommand("test -f " . escapeshellarg($filepath) . " && echo 'exists'");
        return trim($result['output']) === 'exists';
    }

    /**
     * Get file size
     * @param string $filepath File path
     * @return int File size in bytes
     */
    public function getFileSize($filepath) {
        if (!$this->connected) {
            throw new Exception("Not connected to SSH server");
        }

        if (!$this->isPathSafe($filepath)) {
            throw new Exception("Path is outside web root");
        }

        $result = $this->executeCommand("stat -c%s " . escapeshellarg($filepath));

        if (!$result['success']) {
            throw new Exception("Failed to get file size");
        }

        return intval(trim($result['output']));
    }

    /**
     * Validate that a path is within the web root
     * Prevents directory traversal attacks
     * @param string $path Path to validate
     * @return bool Path is safe
     */
    private function isPathSafe($path) {
        // Check for null bytes
        if (strpos($path, "\0") !== false) {
            return false;
        }

        // Check for directory traversal patterns
        if (strpos($path, '..') !== false) {
            return false;
        }

        // Check for encoded traversal attempts
        if (preg_match('/%2e%2e|%252e%252e|\.\.%2f|\.\.%5c/i', $path)) {
            return false;
        }

        // Normalize path: remove duplicate slashes, trailing slash
        $path = preg_replace('#/+#', '/', $path);
        $path = rtrim($path, '/');

        // Convert relative paths to absolute
        if ($path[0] !== '/') {
            $path = $this->web_root . '/' . ltrim($path, '/');
        }

        // Check if normalized path starts with web root
        if (strpos($path, $this->web_root) !== 0) {
            return false;
        }

        // Additional check: ensure no path component is '..'
        $pathParts = explode('/', $path);
        foreach ($pathParts as $part) {
            if ($part === '..' || $part === '.') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get current web root path
     * @return string Web root path
     */
    public function getWebRoot() {
        return $this->web_root;
    }

    /**
     * Close SSH connection
     */
    public function disconnect() {
        if ($this->connection) {
            ssh2_disconnect($this->connection);
            $this->connection = null;
            $this->sftp = null;
            $this->connected = false;
        }
    }

    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct() {
        $this->disconnect();
    }
}
?>
