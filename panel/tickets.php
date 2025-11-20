<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/email.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

$success = '';
$error = '';

// Handle new ticket creation with auto-response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $subject = $_POST['subject'] ?? '';
    $departmentId = $_POST['department_id'] ?? 1;
    $priority = $_POST['priority'] ?? 'medium';
    $message = $_POST['message'] ?? '';

    if ($subject && $departmentId && $message) {
        try {
            $db->beginTransaction();

            // Get department name
            $deptStmt = $db->prepare("SELECT name FROM ticket_departments WHERE id = ?");
            $deptStmt->execute([$departmentId]);
            $department = $deptStmt->fetchColumn() ?: 'General Support';

            // Create ticket with awaiting_admin status
            $stmt = $db->prepare("
                INSERT INTO tickets (customer_id, subject, department_id, department, priority, status, last_reply_by, last_reply_at)
                VALUES (?, ?, ?, ?, ?, 'awaiting_admin', 'client', NOW())
            ");
            $stmt->execute([$customerId, $subject, $departmentId, $department, $priority]);
            $ticketId = $db->lastInsertId();

            // Add first message
            $replyStmt = $db->prepare("
                INSERT INTO ticket_replies (ticket_id, customer_id, message)
                VALUES (?, ?, ?)
            ");
            $replyStmt->execute([$ticketId, $customerId, $message]);

            $db->commit();

            // Send email notification
            $emailService = new EmailService();
            $emailService->sendTicketCreatedNotification($ticketId);

            $success = 'Support ticket created successfully! Our team will respond soon.';
        } catch (PDOException $e) {
            $db->rollBack();
            $error = 'Failed to create ticket: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Handle client reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reply'])) {
    $ticketId = $_POST['ticket_id'] ?? 0;
    $message = $_POST['reply_message'] ?? '';
    
    if ($ticketId && $message) {
        $replyStmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, customer_id, message) VALUES (?, ?, ?)");
        if ($replyStmt->execute([$ticketId, $customerId, $message])) {
            // Update ticket to awaiting_admin
            $updateStmt = $db->prepare("UPDATE tickets SET status='awaiting_admin', last_reply_by='client', last_reply_at=NOW() WHERE id=? AND customer_id=?");
            $updateStmt->execute([$ticketId, $customerId]);

            // Send email notification
            $emailService = new EmailService();
            $emailService->sendTicketReplyNotification($ticketId, 'client');

            $success = 'Reply added successfully!';
        }
    }
}

// Get active departments
$depts = $db->query("SELECT * FROM ticket_departments WHERE status='active' ORDER BY is_default DESC, name ASC")->fetchAll();

// Get all customer tickets with department info
$ticketsStmt = $db->prepare("
    SELECT t.*, d.name as department_name
    FROM tickets t
    LEFT JOIN ticket_departments d ON t.department_id = d.id
    WHERE t.customer_id = ?
    ORDER BY t.created_at DESC
");
$ticketsStmt->execute([$customerId]);
$tickets = $ticketsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-badge-open { background: rgba(0, 212, 255, 0.1); color: var(--primary-color); }
        .status-badge-awaiting-client { background: rgba(255, 170, 0, 0.1); color: var(--warning); font-weight: bold; }
        .status-badge-awaiting-admin { background: rgba(255, 68, 68, 0.1); color: var(--error); }
        .status-badge-resolved { background: rgba(0, 255, 136, 0.1); color: var(--success); }
        .status-badge-closed { background: rgba(128, 128, 128, 0.1); color: #888; }
        .awaiting-client-row { background: rgba(255, 170, 0, 0.05); border-left: 3px solid var(--warning); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Support Tickets</h1>
                    <p class="page-subtitle">Get help from our support team</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Create New Ticket</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div class="form-group">
                                    <label for="subject">Subject *</label>
                                    <input type="text" id="subject" name="subject" required>
                                </div>
                                <div class="form-group">
                                    <label for="department_id">Department *</label>
                                    <select id="department_id" name="department_id" required>
                                        <?php foreach ($depts as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>" <?php echo $dept['is_default'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select id="priority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="6" required placeholder="Describe your issue in detail..."></textarea>
                            </div>

                            <button type="submit" name="create_ticket" class="btn btn-primary">Submit Ticket</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Your Tickets</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($tickets) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Ticket ID</th>
                                            <th>Subject</th>
                                            <th>Department</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Last Reply</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $ticket): 
                                            $rowClass = $ticket['status'] === 'awaiting_client' ? 'awaiting-client-row' : '';
                                        ?>
                                            <tr class="<?php echo $rowClass; ?>">
                                                <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['department_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php
                                                        echo $ticket['priority'] === 'high' ? 'error' :
                                                            ($ticket['priority'] === 'medium' ? 'warning' : 'info');
                                                    ?>">
                                                        <?php echo ucfirst($ticket['priority']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                                        <?php
                                                            $statusLabels = [
                                                                'open' => 'Open',
                                                                'awaiting_client' => 'Awaiting Your Reply',
                                                                'awaiting_admin' => 'Awaiting Support',
                                                                'resolved' => 'Resolved',
                                                                'closed' => 'Closed'
                                                            ];
                                                            echo $statusLabels[$ticket['status']] ?? ucfirst($ticket['status']);
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($ticket['last_reply_at']): ?>
                                                        <small>
                                                            <?php echo ucfirst($ticket['last_reply_by']); ?><br>
                                                            <?php echo date('M j, g:i A', strtotime($ticket['last_reply_at'])); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small>No replies</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><small><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></small></td>
                                                <td>
                                                    <button onclick="viewTicket(<?php echo $ticket['id']; ?>)" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">
                                No support tickets yet. Create one above if you need help.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View/Reply Modal -->
    <div id="ticketModal" class="modal">
        <div class="modal-content" style="max-width:800px;">
            <h2>Ticket Details</h2>
            <div id="ticketContent"></div>
        </div>
    </div>

    <script src="assets/js/mobile-menu.js"></script>
    <script>
        function viewTicket(ticketId) {
            fetch('api/get-ticket.php?id=' + ticketId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTicket(data.ticket, data.replies);
                        document.getElementById('ticketModal').classList.add('active');
                    } else {
                        alert('Error loading ticket');
                    }
                });
        }

        function displayTicket(ticket, replies) {
            let html = '<div style="margin-bottom:20px;">';
            html += '<p><strong>Subject:</strong> ' + ticket.subject + '</p>';
            html += '<p><strong>Department:</strong> ' + (ticket.department_name || 'N/A') + '</p>';
            html += '<p><strong>Status:</strong> <span class="badge">' + ticket.status + '</span></p>';
            html += '<p><strong>Priority:</strong> <span class="badge">' + ticket.priority + '</span></p>';
            html += '</div>';

            html += '<div style="max-height:400px;overflow-y:auto;margin-bottom:20px;">';
            replies.forEach(reply => {
                let isAdmin = reply.admin_id != null;
                html += '<div style="background:' + (isAdmin ? 'var(--surface-light)' : 'var(--background)') + ';padding:15px;margin-bottom:10px;border-radius:6px;">';
                html += '<strong>' + (isAdmin ? 'Support Team' : 'You') + '</strong> <small>' + reply.created_at + '</small><br>';
                html += '<p style="margin-top:10px;">' + reply.message.replace(/\n/g, '<br>') + '</p>';
                html += '</div>';
            });
            html += '</div>';

            if (ticket.status !== 'closed' && ticket.status !== 'resolved') {
                html += '<form method="POST">';
                html += '<input type="hidden" name="ticket_id" value="' + ticket.id + '">';
                html += '<div class="form-group"><label>Add Reply:</label>';
                html += '<textarea name="reply_message" required rows="4" style="width:100%;"></textarea></div>';
                html += '<button type="submit" name="add_reply" class="btn btn-primary">Send Reply</button> ';
            }
            html += '<button type="button" onclick="closeTicketModal()" class="btn btn-secondary">Close</button>';
            if (ticket.status !== 'closed' && ticket.status !== 'resolved') {
                html += '</form>';
            }

            document.getElementById('ticketContent').innerHTML = html;
        }

        function closeTicketModal() {
            document.getElementById('ticketModal').classList.remove('active');
        }

        document.getElementById('ticketModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTicketModal();
            }
        });
    </script>
</body>
</html>
