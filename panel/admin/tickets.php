<?php

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/email.php';

$auth = new Auth();
$auth->requireAdmin();
$db = Database::getInstance()->getConnection();

$success = '';
$error = '';

// Handle new ticket creation by admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket_for_user'])) {
    $customerId = $_POST['customer_id'] ?? 0;
    $subject = $_POST['subject'] ?? '';
    $departmentId = $_POST['department_id'] ?? 1;
    $priority = $_POST['priority'] ?? 'medium';
    $message = $_POST['message'] ?? '';
    $adminId = $auth->getCurrentCustomerId();

    if ($customerId && $subject && $message) {
        try {
            $db->beginTransaction();

            // Get department name
            $deptStmt = $db->prepare("SELECT name FROM ticket_departments WHERE id = ?");
            $deptStmt->execute([$departmentId]);
            $department = $deptStmt->fetchColumn() ?: 'General Support';

            // Create ticket
            $stmt = $db->prepare("
                INSERT INTO tickets (customer_id, subject, department_id, department, priority, status, last_reply_by, last_reply_at)
                VALUES (?, ?, ?, ?, ?, 'open', 'admin', NOW())
            ");
            $stmt->execute([$customerId, $subject, $departmentId, $department, $priority]);
            $ticketId = $db->lastInsertId();

            // Add first message from admin
            $replyStmt = $db->prepare("
                INSERT INTO ticket_replies (ticket_id, admin_id, message)
                VALUES (?, ?, ?)
            ");
            $replyStmt->execute([$ticketId, $adminId, $message]);

            $db->commit();

            // Send email notification
            $emailService = new EmailService();
            $emailService->sendTicketReplyNotification($ticketId, 'admin');

            $success = 'Ticket created successfully for customer!';
        } catch (PDOException $e) {
            $db->rollBack();
            $error = 'Failed to create ticket: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Handle ticket status updates and replies
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $ticketId = $_POST['ticket_id'] ?? 0;
        $status = $_POST['status'] ?? 'open';
        
        $stmt = $db->prepare("UPDATE tickets SET status=? WHERE id=?");
        if ($stmt->execute([$status, $ticketId])) {
            $success = 'Ticket status updated!';
        }
    } elseif (isset($_POST['add_reply'])) {
        $ticketId = $_POST['ticket_id'] ?? 0;
        $message = $_POST['message'] ?? '';
        $adminId = $auth->getCurrentCustomerId();
        
        if ($message && $ticketId) {
            $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, admin_id, message) VALUES (?, ?, ?)");
            if ($stmt->execute([$ticketId, $adminId, $message])) {
                $updateStmt = $db->prepare("UPDATE tickets SET status='awaiting_client', last_reply_by='admin', last_reply_at=NOW() WHERE id=?");
                $updateStmt->execute([$ticketId]);
                $success = 'Reply added successfully!';
            }
        }
    }
}

// Filter parameters
$statusFilter = $_GET['status'] ?? '';
$departmentFilter = $_GET['department'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';

// Build query
$query = "SELECT t.*, c.full_name as customer_name, c.email as customer_email, d.name as department_name
          FROM tickets t
          JOIN customers c ON t.customer_id = c.id
          LEFT JOIN ticket_departments d ON t.department_id = d.id
          WHERE 1=1";

$params = [];
if ($statusFilter) {
    $query .= " AND t.status = ?";
    $params[] = $statusFilter;
}
if ($departmentFilter) {
    $query .= " AND t.department_id = ?";
    $params[] = $departmentFilter;
if ($priorityFilter) {
    $query .= " AND t.priority = ?";
    $params[] = $priorityFilter;
}
}

$query .= " ORDER BY 
            CASE t.status 
                WHEN 'awaiting_admin' THEN 1
                WHEN 'open' THEN 2
                WHEN 'awaiting_client' THEN 3
                WHEN 'resolved' THEN 4
                WHEN 'closed' THEN 5
            END,
            t.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
// Get ticket statistics
$statsQuery = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'awaiting_admin' THEN 1 ELSE 0 END) as awaiting_admin,
    SUM(CASE WHEN status = 'awaiting_client' THEN 1 ELSE 0 END) as awaiting_client,
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
    SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_count
FROM tickets";
$stats = $db->query($statsQuery)->fetch();

// Get departments for filter
$depts = $db->query("SELECT * FROM ticket_departments WHERE status='active' ORDER BY name")->fetchAll();

// Get all customers for ticket creation dropdown
$customers = $db->query("SELECT id, full_name, email FROM customers WHERE status='active' ORDER BY full_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-awaiting-admin { background: rgba(255, 68, 68, 0.1); border-left: 3px solid var(--error); }
        .status-badge-open { background: rgba(0, 212, 255, 0.1); color: var(--primary-color); }
        .status-badge-awaiting-client { background: rgba(255, 170, 0, 0.1); color: var(--warning); }
        .status-badge-awaiting-admin { background: rgba(255, 68, 68, 0.1); color: var(--error); font-weight: bold; }
        .status-badge-resolved { background: rgba(0, 255, 136, 0.1); color: var(--success); }
        .status-badge-closed { background: rgba(128, 128, 128, 0.1); color: #888; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-bar select { padding: 8px 12px; border-radius: 6px; background: var(--surface); border: 1px solid var(--border); color: var(--text-primary); }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal-content {
            background: #0a0e27;
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(0, 212, 255, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: #00d4ff;
        }
        
        .close-modal {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
        }
        
        .close-modal:hover {
            color: #fff;
        }
        
        .customer-select-option {
            padding: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>

        <div class="content">
            <div class="page-header">
                <h1>Support Tickets</h1>
                <button class="btn btn-primary" onclick="openCreateTicketModal()">
                    <i class="fas fa-plus-circle"></i> Create Ticket for User
                </button>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Dashboard -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; color: white;">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Total Tickets</div>
                    <div style="font-size: 32px; font-weight: bold;"><?php echo $stats['total']; ?></div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 10px; color: white; cursor: pointer;" onclick="window.location.href='?status=awaiting_admin'">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-exclamation-circle"></i> Need Response
                    </div>
                    <div style="font-size: 32px; font-weight: bold;"><?php echo $stats['awaiting_admin']; ?></div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 5px;">Click to view</div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); padding: 20px; border-radius: 10px; color: #2d3436; cursor: pointer;" onclick="window.location.href='?status=awaiting_client'">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-clock"></i> Awaiting Client
                    </div>
                    <div style="font-size: 32px; font-weight: bold;"><?php echo $stats['awaiting_client']; ?></div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 5px;">Click to view</div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px; border-radius: 10px; color: white; cursor: pointer;" onclick="window.location.href='?status=open'">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-folder-open"></i> Open
                    </div>
                    <div style="font-size: 32px; font-weight: bold;"><?php echo $stats['open_count']; ?></div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 5px;">Click to view</div>
                </div>

                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 20px; border-radius: 10px; color: white; cursor: pointer;" onclick="window.location.href='?status=resolved'">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-check-circle"></i> Resolved
                    </div>
                    <div style="font-size: 32px; font-weight: bold;"><?php echo $stats['resolved']; ?></div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 5px;">Click to view</div>
                </div>

                <?php if ($stats['high_priority_count'] > 0): ?>
                <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px; border-radius: 10px; color: white; cursor: pointer; border: 2px solid #ff6b6b;" onclick="window.location.href='?priority=high'">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                        <i class="fas fa-fire"></i> High Priority
                    </div>
                    <div style="font-size: 32px; font-weight: bold;"><?php echo $stats['high_priority_count']; ?></div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 5px;">Urgent attention</div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Filter Buttons -->
            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <a href="?" class="btn btn-secondary" style="<?php echo empty($statusFilter) && empty($departmentFilter) ? 'background: var(--primary-color); border-color: var(--primary-color);' : ''; ?>">
                    <i class="fas fa-list"></i> All Tickets
                </a>
                <a href="?status=awaiting_admin" class="btn btn-secondary" style="<?php echo $statusFilter === 'awaiting_admin' ? 'background: var(--error); border-color: var(--error);' : ''; ?>">
                    <i class="fas fa-exclamation-circle"></i> Needs Response (<?php echo $stats['awaiting_admin']; ?>)
                </a>
                <a href="?status=awaiting_client" class="btn btn-secondary" style="<?php echo $statusFilter === 'awaiting_client' ? 'background: var(--warning); border-color: var(--warning);' : ''; ?>">
                    <i class="fas fa-clock"></i> Awaiting Client (<?php echo $stats['awaiting_client']; ?>)
                </a>
                <a href="?status=open" class="btn btn-secondary" style="<?php echo $statusFilter === 'open' ? 'background: var(--info); border-color: var(--info);' : ''; ?>">
                    <i class="fas fa-folder-open"></i> Open (<?php echo $stats['open_count']; ?>)
                </a>
            </div>

            <div class="filter-bar">
                <select onchange="window.location.href='?status='+this.value+'&department=<?php echo htmlspecialchars($departmentFilter); ?>'">
                    <option value="">All Statuses</option>
                    <option value="awaiting_admin" <?php echo $statusFilter === 'awaiting_admin' ? 'selected' : ''; ?>>Awaiting Admin</option>
                    <option value="open" <?php echo $statusFilter === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="awaiting_client" <?php echo $statusFilter === 'awaiting_client' ? 'selected' : ''; ?>>Awaiting Client</option>
                    <option value="resolved" <?php echo $statusFilter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>

                <select onchange="window.location.href='?status=<?php echo htmlspecialchars($statusFilter); ?>&department='+this.value">
                    <option value="">All Departments</option>
                    <?php foreach ($depts as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $departmentFilter == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($statusFilter || $departmentFilter): ?>
                    <a href="tickets.php" class="btn btn-secondary">Clear Filters</a>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <p style="text-align: center; padding: 40px; color: rgba(255,255,255,0.6);">
                            <i class="fas fa-inbox"></i><br>No tickets found
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject</th>
                                        <th>Customer</th>
                                        <th>Department</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Last Reply</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr class="status-<?php echo $ticket['status']; ?>">
                                            <td>#<?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($ticket['customer_name']); ?><br>
                                                <small style="color: rgba(255,255,255,0.5);"><?php echo htmlspecialchars($ticket['customer_email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($ticket['department_name'] ?? 'General'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $ticket['priority'] === 'high' ? 'error' : ($ticket['priority'] === 'low' ? 'info' : 'warning'); ?>">
                                                    <?php echo ucfirst($ticket['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge status-badge-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                                    <?php echo ucwords(str_replace('_', ' ', $ticket['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($ticket['last_reply_by']): ?>
                                                    <?php echo ucfirst($ticket['last_reply_by']); ?><br>
                                                    <small><?php echo date('M d, H:i', strtotime($ticket['last_reply_at'])); ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                            <td>
                                                <a href="ticket-view.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Ticket Modal -->
    <div id="createTicketModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create Ticket for User</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="customer_id">Select Customer *</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">-- Select a customer --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>">
                                <?php echo htmlspecialchars($customer['full_name']); ?> 
                                (<?php echo htmlspecialchars($customer['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required placeholder="Brief description of the issue">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <?php foreach ($depts as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Initial Message *</label>
                    <textarea id="message" name="message" required rows="6" 
                              placeholder="Describe the issue or reason for creating this ticket..."></textarea>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" name="create_ticket_for_user" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus-circle"></i> Create Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        function openCreateTicketModal() {
            document.getElementById('createTicketModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('createTicketModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('createTicketModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
