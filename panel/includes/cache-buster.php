<?php
/**
 * Global cache-buster helpers
 * Used to force clients to reload assets by bumping a shared version token.
 */

if (!class_exists('Database')) {
    require_once __DIR__ . '/database.php';
}

/**
 * Get the current cache-buster version (creates one if missing)
 */
function getCacheBusterVersion() {
    static $version = null;
    if ($version !== null) {
        return $version;
    }

    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT `value` FROM system_settings WHERE `key` = 'cache_buster_version' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();

    if ($row && isset($row['value'])) {
        $version = (string)$row['value'];
        return $version;
    }

    // Create an initial version if missing
    $version = (string)time();
    $insert = $db->prepare("INSERT INTO system_settings (`key`, `value`) VALUES ('cache_buster_version', ?)");
    $insert->execute([$version]);

    return $version;
}

/**
 * Bump the cache-buster version to invalidate cached assets
 */
function bumpCacheBusterVersion() {
    $db = Database::getInstance()->getConnection();
    $version = (string)microtime(true);

    $stmt = $db->prepare("
        INSERT INTO system_settings (`key`, `value`)
        VALUES ('cache_buster_version', ?)
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
    ");
    $stmt->execute([$version]);

    return $version;
}
