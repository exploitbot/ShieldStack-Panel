<?php
/**
 * Database Enhancement Script for ShieldStack Panel
 * Adds new columns to plans and services tables
 */

require_once 'includes/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Connected to database successfully.\n\n";

    // Function to check if column exists
    function columnExists($db, $table, $column) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    echo "=== Enhancing PLANS table ===\n";

    // Add category column if not exists (already exists from schema check)
    if (!columnExists($db, 'plans', 'category')) {
        $db->exec("ALTER TABLE plans ADD COLUMN category VARCHAR(100) DEFAULT 'hosting' AFTER type");
        echo "✓ Added 'category' column\n";
    } else {
        echo "- 'category' column already exists\n";
    }

    // Add databases column
    if (!columnExists($db, 'plans', 'databases')) {
        $db->exec("ALTER TABLE plans ADD COLUMN databases INT DEFAULT 1 AFTER bandwidth");
        echo "✓ Added 'databases' column\n";
    } else {
        echo "- 'databases' column already exists\n";
    }

    // Add email_accounts column
    if (!columnExists($db, 'plans', 'email_accounts')) {
        $db->exec("ALTER TABLE plans ADD COLUMN email_accounts INT DEFAULT 10 AFTER databases");
        echo "✓ Added 'email_accounts' column\n";
    } else {
        echo "- 'email_accounts' column already exists\n";
    }

    // Add subdomains column
    if (!columnExists($db, 'plans', 'subdomains')) {
        $db->exec("ALTER TABLE plans ADD COLUMN subdomains INT DEFAULT 10 AFTER email_accounts");
        echo "✓ Added 'subdomains' column\n";
    } else {
        echo "- 'subdomains' column already exists\n";
    }

    // Add ftp_accounts column
    if (!columnExists($db, 'plans', 'ftp_accounts')) {
        $db->exec("ALTER TABLE plans ADD COLUMN ftp_accounts INT DEFAULT 5 AFTER subdomains");
        echo "✓ Added 'ftp_accounts' column\n";
    } else {
        echo "- 'ftp_accounts' column already exists\n";
    }

    // Add ssl_certificates column
    if (!columnExists($db, 'plans', 'ssl_certificates')) {
        $db->exec("ALTER TABLE plans ADD COLUMN ssl_certificates TINYINT(1) DEFAULT 0 AFTER ftp_accounts");
        echo "✓ Added 'ssl_certificates' column\n";
    } else {
        echo "- 'ssl_certificates' column already exists\n";
    }

    // Add daily_backups column
    if (!columnExists($db, 'plans', 'daily_backups')) {
        $db->exec("ALTER TABLE plans ADD COLUMN daily_backups TINYINT(1) DEFAULT 0 AFTER ssl_certificates");
        echo "✓ Added 'daily_backups' column\n";
    } else {
        echo "- 'daily_backups' column already exists\n";
    }

    // Add support_level column
    if (!columnExists($db, 'plans', 'support_level')) {
        $db->exec("ALTER TABLE plans ADD COLUMN support_level VARCHAR(50) DEFAULT 'standard' AFTER daily_backups");
        echo "✓ Added 'support_level' column\n";
    } else {
        echo "- 'support_level' column already exists\n";
    }

    // Add display_order column
    if (!columnExists($db, 'plans', 'display_order')) {
        $db->exec("ALTER TABLE plans ADD COLUMN display_order INT DEFAULT 0 AFTER support_level");
        echo "✓ Added 'display_order' column\n";
    } else {
        echo "- 'display_order' column already exists\n";
    }

    echo "\n=== Enhancing SERVICES table ===\n";

    // Add expiry_date column
    if (!columnExists($db, 'services', 'expiry_date')) {
        $db->exec("ALTER TABLE services ADD COLUMN expiry_date TIMESTAMP NULL AFTER renewal_date");
        echo "✓ Added 'expiry_date' column\n";
    } else {
        echo "- 'expiry_date' column already exists\n";
    }

    // Add auto_renew column
    if (!columnExists($db, 'services', 'auto_renew')) {
        $db->exec("ALTER TABLE services ADD COLUMN auto_renew TINYINT(1) DEFAULT 1 AFTER expiry_date");
        echo "✓ Added 'auto_renew' column\n";
    } else {
        echo "- 'auto_renew' column already exists\n";
    }

    // Add suspended column
    if (!columnExists($db, 'services', 'suspended')) {
        $db->exec("ALTER TABLE services ADD COLUMN suspended TINYINT(1) DEFAULT 0 AFTER auto_renew");
        echo "✓ Added 'suspended' column\n";
    } else {
        echo "- 'suspended' column already exists\n";
    }

    // Add suspension_reason column
    if (!columnExists($db, 'services', 'suspension_reason')) {
        $db->exec("ALTER TABLE services ADD COLUMN suspension_reason TEXT NULL AFTER suspended");
        echo "✓ Added 'suspension_reason' column\n";
    } else {
        echo "- 'suspension_reason' column already exists\n";
    }

    echo "\n=== Updating existing plans with default values ===\n";

    // Update existing plans with some sensible defaults
    $db->exec("
        UPDATE plans
        SET
            databases = CASE
                WHEN type = 'hosting' THEN 10
                WHEN type = 'vps' THEN 999
                ELSE 1
            END,
            email_accounts = CASE
                WHEN type = 'hosting' THEN 50
                WHEN type = 'vps' THEN 999
                ELSE 5
            END,
            subdomains = CASE
                WHEN type = 'hosting' THEN 25
                WHEN type = 'vps' THEN 999
                ELSE 5
            END,
            ftp_accounts = CASE
                WHEN type = 'hosting' THEN 10
                WHEN type = 'vps' THEN 999
                ELSE 3
            END,
            ssl_certificates = CASE
                WHEN price >= 20 THEN 1
                ELSE 0
            END,
            daily_backups = CASE
                WHEN price >= 20 THEN 1
                ELSE 0
            END,
            support_level = CASE
                WHEN price >= 40 THEN 'priority'
                WHEN price >= 20 THEN 'standard'
                ELSE 'basic'
            END,
            display_order = id
        WHERE databases IS NULL OR databases = 1
    ");
    echo "✓ Updated existing plans with default values\n";

    // Update existing services with expiry dates based on renewal dates
    $db->exec("
        UPDATE services
        SET expiry_date = renewal_date
        WHERE expiry_date IS NULL AND renewal_date IS NOT NULL
    ");
    echo "✓ Updated existing services with expiry dates\n";

    echo "\n=== Database enhancement completed successfully! ===\n";
    echo "\nYou can now delete this file: enhance_database.php\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
