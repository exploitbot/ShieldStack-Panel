-- ShieldStack Panel MySQL Setup Script
-- Run this script as MySQL root user

CREATE DATABASE IF NOT EXISTS shieldstack_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'shieldstack'@'localhost' IDENTIFIED BY 'ShieldStack2024!';

GRANT ALL PRIVILEGES ON shieldstack_panel.* TO 'shieldstack'@'localhost';

FLUSH PRIVILEGES;

USE shieldstack_panel;

SELECT 'Database and user created successfully!' AS status;
