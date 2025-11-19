<?php
require_once '../includes/auth.php';
require_once '../includes/cache-buster.php';

$auth = new Auth();
$auth->requireAdmin();

header('Content-Type: application/json');

try {
    $version = bumpCacheBusterVersion();

    echo json_encode([
        'success' => true,
        'version' => $version,
        'message' => 'Cache cleared for all users. Their browsers will fetch fresh assets.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
