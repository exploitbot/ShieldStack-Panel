<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/email.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$ticketId = $_GET['id'] ?? 0;

$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'] ?? '';

    if ($status) {
        $stmt = $db->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $ticketId])) {
            $success = 'Ticket status updated successfully!';
        } else {
            $error = 'Failed to update status.';
        }
    }
}

// Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reply'])) {
    $message = $_POST['message'] ?? '';
    $adminId = $auth->getCurrentCustomerId();

    if ($message && $ticketId) {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, admin_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$ticketId, $adminId, $message]);

            // Update ticket status and last reply
            $updateStmt = $db->prepare("UPDATE tickets SET status = 'awaiting_client', last_reply_by = 'admin', last_reply_at = NOW() WHERE id = ?");
            $updateStmt->execute([$ticketId]);

            $db->commit();

            // Send email notification
            $emailService = new EmailService();
            $emailService->sendTicketReplyNotification($ticketId, 'admin');

            $success = 'Reply added successfully!';
        } catch (PDOException $e) {
            $db->rollBack();
            $error = 'Failed to add reply: ' . $e->getMessage();
        }
    }
}

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
    header('Location: tickets.php');
    exit;
}

// Get replies
$repliesStmt = $db->prepare("
    SELECT r.*,
           c.full_name as customer_name,
           a.full_name as admin_name
    FROM ticket_replies r
    LEFT JOIN customers c ON r.customer_id = c.id
    LEFT JOIN customers a ON r.admin_id = a.id
    WHERE r.ticket_id = ?
    ORDER BY r.created_at ASC
");
$repliesStmt->execute([$ticketId]);
$replies = $repliesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket #<?php echo $ticketId; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .ticket-header {
            background: var(--surface);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .ticket-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .meta-item {
            padding: 10px;
            background: var(--background);
            border-radius: 6px;
        }
        .meta-label {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .meta-value {
            font-size: 16px;
            font-weight: 500;
        }
        .reply-container {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
        }
        .reply-admin {
            background: var(--surface-light);
            border-left: 3px solid var(--primary-color);
        }
        .reply-customer {
            background: var(--surface);
            border-left: 3px solid var(--text-secondary);
        }
        .reply-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .reply-author {
            font-weight: 600;
        }
        .reply-time {
            color: var(--text-secondary);
        }
        .reply-message {
            line-height: 1.6;
            white-space: pre-line;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Ticket #<?php echo $ticketId; ?></h1>
                    <a href="tickets.php" class="btn btn-secondary">← Back to Tickets</a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Ticket Header -->
                <div class="ticket-header">
                    <h2><?php echo htmlspecialchars($ticket['subject']); ?></h2>

                    <div class="ticket-meta">
                        <div class="meta-item">
                            <div class="meta-label">Customer</div>
                            <div class="meta-value"><?php echo htmlspecialchars($ticket['customer_name']); ?></div>
                            <small><?php echo htmlspecialchars($ticket['customer_email']); ?></small>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Department</div>
                            <div class="meta-value"><?php echo htmlspecialchars($ticket['department_name'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Priority</div>
                            <div class="meta-value">
                                <span class="badge badge-<?php
                                    echo $ticket['priority'] === 'high' ? 'error' :
                                        ($ticket['priority'] === 'medium' ? 'warning' : 'info');
                                ?>">
                                    <?php echo ucfirst($ticket['priority']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Status</div>
                            <div class="meta-value">
                                <span class="badge badge-<?php
                                    echo $ticket['status'] === 'closed' ? 'secondary' :
                                        ($ticket['status'] === 'resolved' ? 'success' : 'primary');
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Created</div>
                            <div class="meta-value"><?php echo date('M d, Y g:i A', strtotime($ticket['created_at'])); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Last Reply</div>
                            <div class="meta-value">
                                <?php if ($ticket['last_reply_at']): ?>
                                    <?php echo ucfirst($ticket['last_reply_by']); ?><br>
                                    <small><?php echo date('M d, Y g:i A', strtotime($ticket['last_reply_at'])); ?></small>
                                <?php else: ?>
                                    No replies yet
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Conversation</h2>
                    </div>
                    <div class="card-body">
                        <?php foreach ($replies as $reply): ?>
                            <div class="reply-container <?php echo $reply['admin_id'] ? 'reply-admin' : 'reply-customer'; ?>">
                                <div class="reply-header">
                                    <span class="reply-author">
                                        <?php if ($reply['admin_id']): ?>
                                            <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($reply['admin_name'] ?? 'Support Team'); ?>
                                        <?php else: ?>
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($reply['customer_name']); ?>
                                        <?php endif; ?>
                                    </span>
                                    <span class="reply-time"><?php echo date('M d, Y g:i A', strtotime($reply['created_at'])); ?></span>
                                </div>
                                <div class="reply-message">
                                    <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($replies) === 0): ?>
                            <p style="text-align: center; color: var(--text-secondary); padding: 20px;">
                                No messages yet.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reply Form -->
                <?php if ($ticket['status'] !== 'closed'): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Add Reply</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="6" required placeholder="Type your reply here..."></textarea>
                            </div>
                            <button type="submit" name="add_reply" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Update Status</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                            <select name="status" class="form-control" style="max-width: 200px;">
                                <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="awaiting_client" <?php echo $ticket['status'] === 'awaiting_client' ? 'selected' : ''; ?>>Awaiting Client</option>
                                <option value="awaiting_admin" <?php echo $ticket['status'] === 'awaiting_admin' ? 'selected' : ''; ?>>Awaiting Admin</option>
                                <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-secondary">Update Status</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    This ticket is closed and cannot receive new replies.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
