<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

// Get all customers
$customersStmt = $db->query("
    SELECT c.*,
           (SELECT COUNT(*) FROM services WHERE customer_id = c.id) as service_count,
           (SELECT COUNT(*) FROM tickets WHERE customer_id = c.id) as ticket_count
    FROM customers c
    WHERE c.is_admin = 0
    ORDER BY c.created_at DESC
");
$customers = $customersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Customers</h1>
                    <p class="page-subtitle">Manage customer accounts</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">All Customers (<?php echo count($customers); ?>)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($customers) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Company</th>
                                            <th>Services</th>
                                            <th>Tickets</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td>#<?php echo $customer['id']; ?></td>
                                                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['company'] ?? 'N/A'); ?></td>
                                                <td><?php echo $customer['service_count']; ?></td>
                                                <td><?php echo $customer['ticket_count']; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $customer['status'] === 'active' ? 'success' : 'error'; ?>">
                                                        <?php echo htmlspecialchars($customer['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-secondary); text-align: center; padding: 40px;">No customers yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
