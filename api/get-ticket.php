<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = Database::getInstance()->getConnection();
$ticketId = $_GET['id'] ?? 0;

// Get ticket details
$stmt = $db->prepare("
    SELECT t.*, c.full_name as customer_name, c.email as customer_email, d.name as department_name
    FROM tickets t
    JOIN customers c ON t.customer_id = c.id
    LEFT JOIN ticket_departments d ON t.department_id = d.id
    WHERE t.id = ?
");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found']);
    exit;
}

// Check permissions
$isAdmin = $auth->isAdmin();
$isOwner = $ticket['customer_id'] == $auth->getCurrentCustomerId();

if (!$isAdmin && !$isOwner) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Get replies
$repliesStmt = $db->prepare("
    SELECT r.*, c.full_name as author_name
    FROM ticket_replies r
    LEFT JOIN customers c ON (r.customer_id = c.id OR r.admin_id = c.id)
    WHERE r.ticket_id = ?
    ORDER BY r.created_at ASC
");
$repliesStmt->execute([$ticketId]);
$replies = $repliesStmt->fetchAll();

echo json_encode([
    'success' => true,
    'ticket' => $ticket,
    'replies' => $replies
]);
