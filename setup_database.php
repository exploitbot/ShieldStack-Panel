<?php
/**
 * Database Setup Helper
 * This script helps you set up the MySQL database for ShieldStack Panel
 */

echo "<!DOCTYPE html><html><head><title>ShieldStack Panel - Database Setup</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#0f0f1e;color:#fff;}";
echo ".box{background:#16213e;border:1px solid #2a3a5f;border-radius:10px;padding:30px;margin:20px 0;}";
echo "h1{color:#00d4ff;}h2{color:#00d4ff;margin-top:30px;}";
echo "pre{background:#0f0f1e;padding:15px;border-radius:5px;overflow-x:auto;border:1px solid #2a3a5f;}";
echo ".success{color:#00ff88;}.error{color:#ff4444;}.warning{color:#ffaa00;}";
echo "code{background:#0f0f1e;padding:2px 6px;border-radius:3px;}</style></head><body>";

echo "<h1>🛡️ ShieldStack Panel - Database Setup</h1>";

echo "<div class='box'>";
echo "<h2>Step 1: Run the SQL Setup Script</h2>";
echo "<p>You need to run the following SQL commands as the MySQL root user:</p>";
echo "<pre>mysql -u root -p < /var/www/html/panel/setup_mysql.sql</pre>";
echo "<p>Or manually run these commands:</p>";
echo "<pre>";
echo "CREATE DATABASE IF NOT EXISTS shieldstack_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
echo "CREATE USER IF NOT EXISTS 'shieldstack'@'localhost' IDENTIFIED BY 'ShieldStack2024!';\n";
echo "GRANT ALL PRIVILEGES ON shieldstack_panel.* TO 'shieldstack'@'localhost';\n";
echo "FLUSH PRIVILEGES;";
echo "</pre>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>Step 2: Test Database Connection</h2>";

// Try to connect
require_once 'includes/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p class='success'><strong>✓ Database connection successful!</strong></p>";
    echo "<p>Database: <code>" . DB_NAME . "</code></p>";
    echo "<p>User: <code>" . DB_USER . "</code></p>";
    echo "<p>Host: <code>" . DB_HOST . "</code></p>";
    
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p class='success'><strong>✓ Database tables found (" . count($tables) . ")</strong></p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        // Check admin account
        $adminCheck = $pdo->query("SELECT COUNT(*) as count FROM customers WHERE is_admin = 1")->fetch();
        if ($adminCheck['count'] > 0) {
            echo "<p class='success'><strong>✓ Admin account exists</strong></p>";
        }
        
        echo "<hr>";
        echo "<p><strong>✅ Setup Complete!</strong></p>";
        echo "<p>You can now access the panel:</p>";
        echo "<ul>";
        echo "<li><a href='login.php' style='color:#00d4ff'>Customer Login</a></li>";
        echo "<li><a href='admin/dashboard.php' style='color:#00d4ff'>Admin Panel</a></li>";
        echo "</ul>";
        echo "<p>Default admin credentials:</p>";
        echo "<pre>Email: admin@shieldstack.dev\nPassword: Admin123!</pre>";
        
    } else {
        echo "<p class='warning'><strong>⚠ Database connected but no tables found</strong></p>";
        echo "<p>The tables will be created automatically when you first access the panel.</p>";
        echo "<p><a href='login.php' style='color:#00d4ff'>Go to Login Page</a> (this will initialize the database)</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'><strong>✗ Database connection failed!</strong></p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    $errorCode = $e->getCode();
    
    if ($errorCode == 1049) {
        echo "<p class='warning'>The database <code>shieldstack_panel</code> does not exist.</p>";
        echo "<p>Please run the SQL setup script from Step 1 above.</p>";
    } elseif ($errorCode == 1045) {
        echo "<p class='warning'>Access denied for user. Please check your credentials.</p>";
        echo "<p>Update the credentials in: <code>/var/www/html/panel/includes/config.php</code></p>";
    } elseif ($errorCode == 2002) {
        echo "<p class='warning'>Cannot connect to MySQL server.</p>";
        echo "<p>Make sure MySQL is running: <code>systemctl status mysqld</code></p>";
    }
}

echo "</div>";

echo "<div class='box'>";
echo "<h2>Alternative: Use Existing Database Credentials</h2>";
echo "<p>If you already have a MySQL database and user, update the file:</p>";
echo "<pre>/var/www/html/panel/includes/config.php</pre>";
echo "<p>With your existing credentials.</p>";
echo "</div>";

echo "<div class='box'>";
echo "<p style='text-align:center;color:#888;font-size:12px;'>Delete this file after setup: setup_database.php</p>";
echo "</div>";

echo "</body></html>";
?>
