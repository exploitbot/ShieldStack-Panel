<?php
/**
 * Backup Manager
 * Manages file backups and cleanup
 */

class BackupManager {
    private $db;
    private $retentionDays = 30;

    public function __construct() {
        require_once __DIR__ . '/../../panel/includes/database.php';
        $this->db = Database::getInstance()->getConnection();
        $this->loadSettings();
    }

    /**
     * Load backup settings
     */
    private function loadSettings() {
        $stmt = $this->db->prepare("SELECT `value` FROM system_settings WHERE `key` = 'ai_backup_retention_days'");
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $this->retentionDays = intval($result['value']) ?: 30;
        }
    }

    /**
     * Record a backup in the database
     * @param int $customerId Customer ID
     * @param int $changeLogId Change log ID
     * @param string $filePath Original file path
     * @param string $backupPath Backup file path
     * @param int $fileSize File size in bytes
     * @return int Backup record ID
     */
    public function recordBackup($customerId, $changeLogId, $filePath, $backupPath, $fileSize = 0) {
        try {
            // Calculate expiration date
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->retentionDays} days"));

            // Calculate file checksum if size is reasonable
            $checksum = null;
            if ($fileSize > 0 && $fileSize < 10 * 1024 * 1024) { // Less than 10MB
                // We can't calculate checksum here without file content
                // This would be done by the caller
            }

            $stmt = $this->db->prepare("
                INSERT INTO ai_file_backups
                (customer_id, change_log_id, file_path, backup_path, file_size, checksum, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $customerId,
                $changeLogId,
                $filePath,
                $backupPath,
                $fileSize,
                $checksum,
                $expiresAt
            ]);

            return $this->db->lastInsertId();

        } catch (Exception $e) {
            error_log("Failed to record backup: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get backups for a customer
     * @param int $customerId Customer ID
     * @param int $limit Maximum number of backups to return
     * @return array List of backups
     */
    public function getCustomerBackups($customerId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT
                b.*,
                c.user_request,
                c.executed_at as change_executed_at
            FROM ai_file_backups b
            LEFT JOIN ai_change_logs c ON b.change_log_id = c.id
            WHERE b.customer_id = ?
            ORDER BY b.created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$customerId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get backups for a specific file
     * @param int $customerId Customer ID
     * @param string $filePath File path
     * @return array List of backups
     */
    public function getFileBackups($customerId, $filePath) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ai_file_backups
            WHERE customer_id = ? AND file_path = ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([$customerId, $filePath]);
        return $stmt->fetchAll();
    }

    /**
     * Clean up expired backups
     * Should be run periodically (cron job)
     * @return array ['deleted_count' => int, 'errors' => array]
     */
    public function cleanupExpiredBackups() {
        $deleted = 0;
        $errors = [];

        try {
            // Get expired backups
            $stmt = $this->db->prepare("
                SELECT *
                FROM ai_file_backups
                WHERE expires_at < NOW()
            ");
            $stmt->execute();
            $expiredBackups = $stmt->fetchAll();

            foreach ($expiredBackups as $backup) {
                try {
                    // Delete from database
                    $deleteStmt = $this->db->prepare("DELETE FROM ai_file_backups WHERE id = ?");
                    $deleteStmt->execute([$backup['id']]);
                    $deleted++;

                    // Note: Actual file deletion should be done via SSH if needed
                    // For now, we just remove from database

                } catch (Exception $e) {
                    $errors[] = "Failed to delete backup {$backup['id']}: " . $e->getMessage();
                }
            }

        } catch (Exception $e) {
            $errors[] = "Cleanup failed: " . $e->getMessage();
        }

        return [
            'deleted_count' => $deleted,
            'errors' => $errors
        ];
    }

    /**
     * Get backup statistics for a customer
     * @param int $customerId Customer ID
     * @return array Statistics
     */
    public function getBackupStats($customerId) {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_backups,
                SUM(file_size) as total_size,
                MAX(created_at) as latest_backup,
                COUNT(CASE WHEN expires_at < NOW() THEN 1 END) as expired_count
            FROM ai_file_backups
            WHERE customer_id = ?
        ");

        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }

    /**
     * Restore a file from backup
     * Note: This returns backup info, actual restoration is done via SSH
     * @param int $backupId Backup ID
     * @param int $customerId Customer ID (for security)
     * @return array|null Backup info
     */
    public function getBackupForRestore($backupId, $customerId) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ai_file_backups
            WHERE id = ? AND customer_id = ?
        ");

        $stmt->execute([$backupId, $customerId]);
        return $stmt->fetch();
    }

    /**
     * Mark a backup as restored
     * @param int $backupId Backup ID
     */
    public function markAsRestored($backupId) {
        // Could add a 'restored_at' column to track this
        // For now, just log it
        error_log("Backup {$backupId} was restored");
    }
}
?>
