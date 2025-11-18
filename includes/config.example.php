<?php
// Database Configuration
// IMPORTANT: Copy this file to config.php and update with your actual credentials
// Never commit config.php to version control!

// Reads from environment variables (for Vercel, Docker, etc.) with fallback defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'shieldstack_panel');
define('DB_USER', getenv('DB_USER') ?: 'your_database_user');
define('DB_PASS', getenv('DB_PASS') ?: 'your_secure_password_here');
define('DB_CHARSET', 'utf8mb4');

// Application Environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') ?: (APP_ENV === 'development'));
?>
