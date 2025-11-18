<?php
/**
 * Get Session API
 * Retrieves chat session history
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/database.php';

session_start();
$auth = new Auth();

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

try {
    $sessionId = $_GET['session_id'] ?? '';

    if (empty($sessionId)) {
        throw new Exception('Session ID required');
    }

    // Get session
    $stmt = $db->prepare("
        SELECT *
        FROM ai_chat_sessions
        WHERE session_id = ? AND customer_id = ?
    ");
    $stmt->execute([$sessionId, $customerId]);
    $session = $stmt->fetch();

    if (!$session) {
        throw new Exception('Session not found');
    }

    $messages = json_decode($session['messages'], true) ?: [];

    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'messages' => $messages,
        'total_tokens' => $session['total_tokens_used']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
