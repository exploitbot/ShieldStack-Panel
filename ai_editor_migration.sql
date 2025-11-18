-- AI Website Editor Migration Script
-- Run this script to add AI website editor functionality to ShieldStack Panel
-- Created: 2025-11-18

-- ============================================
-- AI Service Plans Table
-- ============================================
CREATE TABLE IF NOT EXISTS `ai_service_plans` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `plan_type` ENUM('basic', 'pro', 'enterprise') NOT NULL,
    `token_limit` INT NOT NULL,
    `tokens_used` INT DEFAULT 0,
    `status` ENUM('active', 'suspended', 'expired') DEFAULT 'active',
    `activated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_plan_type` (`plan_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SSH Credentials Table (Encrypted)
-- ============================================
CREATE TABLE IF NOT EXISTS `customer_ssh_credentials` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `website_name` VARCHAR(255) DEFAULT NULL,
    `ssh_host` VARCHAR(255) NOT NULL,
    `ssh_port` INT DEFAULT 22,
    `ssh_username` VARCHAR(255) NOT NULL,
    `ssh_password_encrypted` TEXT,
    `ssh_key_encrypted` TEXT,
    `web_root_path` VARCHAR(500) DEFAULT '/var/www/html',
    `website_url` VARCHAR(255) DEFAULT NULL,
    `website_type` ENUM('wordpress', 'html', 'php', 'laravel', 'react', 'custom') DEFAULT 'custom',
    `is_active` TINYINT(1) DEFAULT 1,
    `last_connected` TIMESTAMP NULL,
    `connection_status` VARCHAR(50) DEFAULT 'not_tested',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AI Change Logs Table
-- ============================================
CREATE TABLE IF NOT EXISTS `ai_change_logs` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `session_id` VARCHAR(100) DEFAULT NULL,
    `ssh_credential_id` INT DEFAULT NULL,
    `user_request` TEXT NOT NULL,
    `ai_response` TEXT,
    `files_modified` JSON,
    `commands_executed` JSON,
    `backup_path` VARCHAR(500) DEFAULT NULL,
    `success` TINYINT(1) DEFAULT 1,
    `error_message` TEXT,
    `tokens_used` INT DEFAULT 0,
    `execution_time_ms` INT DEFAULT 0,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ssh_credential_id`) REFERENCES `customer_ssh_credentials`(`id`) ON DELETE SET NULL,
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_session` (`session_id`),
    INDEX `idx_success` (`success`),
    INDEX `idx_executed_at` (`executed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AI Chat Sessions Table
-- ============================================
CREATE TABLE IF NOT EXISTS `ai_chat_sessions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `session_id` VARCHAR(100) UNIQUE NOT NULL,
    `ssh_credential_id` INT DEFAULT NULL,
    `session_name` VARCHAR(255) DEFAULT 'Untitled Chat',
    `messages` JSON,
    `total_tokens_used` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_message_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ssh_credential_id`) REFERENCES `customer_ssh_credentials`(`id`) ON DELETE SET NULL,
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_session` (`session_id`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AI Rate Limiting Table
-- ============================================
CREATE TABLE IF NOT EXISTS `ai_rate_limits` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `request_count` INT DEFAULT 0,
    `tokens_today` INT DEFAULT 0,
    `last_request_at` TIMESTAMP NULL,
    `reset_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_customer_rate_limit` (`customer_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    INDEX `idx_reset_at` (`reset_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AI File Backups Table
-- ============================================
CREATE TABLE IF NOT EXISTS `ai_file_backups` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `change_log_id` INT DEFAULT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `backup_path` VARCHAR(500) NOT NULL,
    `file_size` BIGINT DEFAULT 0,
    `checksum` VARCHAR(64) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`change_log_id`) REFERENCES `ai_change_logs`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_change_log` (`change_log_id`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AI System Settings
-- ============================================
INSERT INTO `system_settings` (`key`, `value`) VALUES
('ai_editor_enabled', '1'),
('ai_openai_endpoint', ''),
('ai_openai_key', ''),
('ai_max_requests_per_minute', '10'),
('ai_max_tokens_per_day', '50000'),
('ai_backup_retention_days', '30'),
('ai_max_file_size_mb', '5')
ON DUPLICATE KEY UPDATE `key` = `key`;

-- ============================================
-- Sample AI Plans (Optional - Comment out if not needed)
-- ============================================
-- These are example plans - adjust pricing and limits as needed
/*
INSERT INTO `plans` (`name`, `type`, `category`, `description`, `price`, `billing_cycle`, `features`, `display_order`, `status`)
VALUES
('AI Basic', 'ai-editor', 'ai-services', 'AI-powered website editing with 10,000 tokens/month', 29.99, 'monthly',
 '["10,000 AI Tokens/month","Basic website modifications","Automatic backups","Email support"]',
 100, 'active'),

('AI Pro', 'ai-editor', 'ai-services', 'AI-powered website editing with 50,000 tokens/month', 79.99, 'monthly',
 '["50,000 AI Tokens/month","Advanced website modifications","Priority AI processing","Automatic backups","Priority support"]',
 101, 'active'),

('AI Enterprise', 'ai-editor', 'ai-services', 'Unlimited AI-powered website editing', 199.99, 'monthly',
 '["Unlimited AI Tokens","Full website control","Real-time processing","Automatic backups","24/7 Premium support","Dedicated account manager"]',
 102, 'active')
ON DUPLICATE KEY UPDATE `name` = `name`;
*/

-- ============================================
-- Verification Queries
-- ============================================
-- Run these after migration to verify tables were created:
-- SELECT COUNT(*) as ai_plans FROM ai_service_plans;
-- SELECT COUNT(*) as ssh_creds FROM customer_ssh_credentials;
-- SELECT COUNT(*) as chat_sessions FROM ai_chat_sessions;
-- SELECT COUNT(*) as change_logs FROM ai_change_logs;
-- SELECT COUNT(*) as rate_limits FROM ai_rate_limits;
-- SELECT COUNT(*) as backups FROM ai_file_backups;

-- ============================================
-- Migration Complete
-- ============================================
-- Next steps:
-- 1. Run this migration script on your database
-- 2. Update includes/config.php with AI API credentials
-- 3. Ensure PHP ssh2 extension is installed (pecl install ssh2)
-- 4. Set proper file permissions for the ai-editor directory
-- ============================================
