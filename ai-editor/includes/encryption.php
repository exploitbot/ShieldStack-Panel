<?php
/**
 * Credential Encryption Helper
 * Handles encryption and decryption of sensitive SSH credentials
 * Uses AES-256-CBC encryption with random IV
 */

class CredentialEncryption {
    private $encryption_key;
    private $cipher_method = 'aes-256-cbc';

    public function __construct() {
        // Get encryption key from system settings or environment
        $this->encryption_key = $this->getEncryptionKey();

        if (empty($this->encryption_key)) {
            throw new Exception('SSH encryption key not configured. Please set AI_SSH_ENCRYPTION_KEY in system settings.');
        }
    }

    /**
     * Get encryption key from system settings or generate one
     */
    private function getEncryptionKey() {
        // Check environment variable first
        $envKey = getenv('AI_SSH_ENCRYPTION_KEY');
        if ($envKey) {
            return $envKey;
        }

        // Check system settings
        require_once __DIR__ . '/../../includes/database.php';
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT value FROM system_settings WHERE `key` = 'ai_ssh_encryption_key'");
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result && !empty($result['value'])) {
            return $result['value'];
        }

        // Generate new key and store it
        $newKey = bin2hex(random_bytes(32));
        $stmt = $db->prepare("INSERT INTO system_settings (`key`, `value`) VALUES ('ai_ssh_encryption_key', ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$newKey, $newKey]);

        return $newKey;
    }

    /**
     * Encrypt sensitive data
     * @param string $data Data to encrypt
     * @return string Base64 encoded encrypted data with IV
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        $iv_length = openssl_cipher_iv_length($this->cipher_method);
        $iv = openssl_random_pseudo_bytes($iv_length);

        $encrypted = openssl_encrypt(
            $data,
            $this->cipher_method,
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }

        // Combine IV and encrypted data, then base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt encrypted data
     * @param string $encrypted_data Base64 encoded encrypted data with IV
     * @return string Decrypted data
     */
    public function decrypt($encrypted_data) {
        if (empty($encrypted_data)) {
            return '';
        }

        $data = base64_decode($encrypted_data);
        if ($data === false) {
            throw new Exception('Invalid encrypted data format');
        }

        $iv_length = openssl_cipher_iv_length($this->cipher_method);
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);

        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher_method,
            $this->encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Hash data using SHA-256
     * Used for checksums and file verification
     */
    public function hash($data) {
        return hash('sha256', $data);
    }

    /**
     * Verify hash matches data
     */
    public function verifyHash($data, $hash) {
        return hash_equals($hash, $this->hash($data));
    }
}
?>
