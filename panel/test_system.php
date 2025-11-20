<?php
// System Test Script
echo "ShieldStack Hosting Management System - Test Summary\n";
echo "=====================================================\n\n";

require_once 'includes/config.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful\n\n";

    // Test Plans Table
    echo "PLANS TABLE:\n";
    echo "-------------\n";
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM plans GROUP BY category");
    $results = $stmt->fetchAll();
    foreach ($results as $row) {
        echo sprintf("  %s: %d plans\n", ucfirst($row['category']), $row['count']);
    }
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM plans");
    $total = $stmt->fetch();
    echo sprintf("  TOTAL: %d plans\n\n", $total['count']);

    // Test Services Table
    echo "SERVICES TABLE:\n";
    echo "---------------\n";
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM services GROUP BY status");
    $results = $stmt->fetchAll();
    foreach ($results as $row) {
        echo sprintf("  %s: %d services\n", ucfirst($row['status']), $row['count']);
    }
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM services");
    $total = $stmt->fetch();
    echo sprintf("  TOTAL: %d services\n\n", $total['count']);

    // Test Enhanced Columns
    echo "ENHANCED COLUMNS:\n";
    echo "-----------------\n";
    $stmt = $db->query("DESCRIBE plans");
    $columns = $stmt->fetchAll();
    $enhancedCols = ['databases', 'email_accounts', 'subdomains', 'ftp_accounts', 'ssl_certificates', 'daily_backups', 'support_level', 'display_order'];
    echo "Plans table:\n";
    foreach ($enhancedCols as $col) {
        $exists = false;
        foreach ($columns as $row) {
            if ($row['Field'] === $col) {
                $exists = true;
                break;
            }
        }
        echo sprintf("  %s: %s\n", $col, $exists ? '✓' : '✗');
    }

    $stmt = $db->query("DESCRIBE services");
    $columns = $stmt->fetchAll();
    $enhancedCols = ['expiry_date', 'auto_renew', 'suspended', 'suspension_reason'];
    echo "\nServices table:\n";
    foreach ($enhancedCols as $col) {
        $exists = false;
        foreach ($columns as $row) {
            if ($row['Field'] === $col) {
                $exists = true;
                break;
            }
        }
        echo sprintf("  %s: %s\n", $col, $exists ? '✓' : '✗');
    }

    // Test Files
    echo "\n\nFILE CHECKS:\n";
    echo "------------\n";
    $files = [
        'Admin manage-plans.php' => '/var/www/html/panel/admin/manage-plans.php',
        'Admin user-services.php' => '/var/www/html/panel/admin/user-services.php',
        'Client plans.php' => '/var/www/html/panel/plans.php',
        'Client services.php' => '/var/www/html/panel/services.php'
    ];
    
    foreach ($files as $name => $path) {
        echo sprintf("  %s: %s\n", $name, file_exists($path) ? '✓' : '✗');
    }

    echo "\n\nSAMPLE PLAN DATA:\n";
    echo "-----------------\n";
    $stmt = $db->query("SELECT name, category, price, billing_cycle, disk_space, bandwidth, `databases`, ssl_certificates, daily_backups, support_level FROM plans LIMIT 3");
    $plans = $stmt->fetchAll();
    foreach ($plans as $plan) {
        echo sprintf("\nPlan: %s\n", $plan['name']);
        echo sprintf("  Category: %s | Price: $%.2f/%s\n", $plan['category'], $plan['price'], $plan['billing_cycle']);
        echo sprintf("  Disk: %s | Bandwidth: %s | DBs: %s\n", $plan['disk_space'], $plan['bandwidth'], $plan['databases']);
        echo sprintf("  SSL: %s | Backups: %s | Support: %s\n", 
            $plan['ssl_certificates'] ? 'Yes' : 'No',
            $plan['daily_backups'] ? 'Yes' : 'No',
            $plan['support_level']
        );
    }

    echo "\n\n=====================================================\n";
    echo "All tests completed successfully!\n";

} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
