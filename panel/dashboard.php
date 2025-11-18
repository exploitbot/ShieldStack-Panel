<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

// Get customer stats
$servicesStmt = $db->prepare("SELECT COUNT(*) as count FROM services WHERE customer_id = ?");
$servicesStmt->execute([$customerId]);
$servicesCount = $servicesStmt->fetch()['count'];

$activeServicesStmt = $db->prepare("SELECT COUNT(*) as count FROM services WHERE customer_id = ? AND status = 'active'");
$activeServicesStmt->execute([$customerId]);
$activeServicesCount = $activeServicesStmt->fetch()['count'];

$ticketsStmt = $db->prepare("SELECT COUNT(*) as count FROM tickets WHERE customer_id = ?");
$ticketsStmt->execute([$customerId]);
$ticketsCount = $ticketsStmt->fetch()['count'];

$openTicketsStmt = $db->prepare("SELECT COUNT(*) as count FROM tickets WHERE customer_id = ? AND status = 'open'");
$openTicketsStmt->execute([$customerId]);
$openTicketsCount = $openTicketsStmt->fetch()['count'];

$invoicesStmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE customer_id = ?");
$invoicesStmt->execute([$customerId]);
$invoicesCount = $invoicesStmt->fetch()['count'];

$unpaidInvoicesStmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE customer_id = ? AND status = 'unpaid'");
$unpaidInvoicesStmt->execute([$customerId]);
$unpaidInvoicesCount = $unpaidInvoicesStmt->fetch()['count'];

// Get recent services
$recentServicesStmt = $db->prepare("
    SELECT s.*, p.name as plan_name, p.price, p.billing_cycle
    FROM services s
    JOIN plans p ON s.plan_id = p.id
    WHERE s.customer_id = ?
    ORDER BY s.created_at DESC
    LIMIT 5
");
$recentServicesStmt->execute([$customerId]);
$recentServices = $recentServicesStmt->fetchAll();

// Get recent tickets
$recentTicketsStmt = $db->prepare("
    SELECT * FROM tickets
    WHERE customer_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$recentTicketsStmt->execute([$customerId]);
$recentTickets = $recentTicketsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($auth->getCurrentCustomerName()); ?></p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <span>🖥️</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $activeServicesCount; ?></h3>
                            <p>Active Services</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <span>📦</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $servicesCount; ?></h3>
                            <p>Total Services</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <span>🎫</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $openTicketsCount; ?></h3>
                            <p>Open Tickets</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon red">
                            <span>💳</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $unpaidInvoicesCount; ?></h3>
                            <p>Unpaid Invoices</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Services</h2>
                        <a href="services.php" class="btn btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($recentServices) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Domain</th>
                                            <th>Status</th>
                                            <th>Price</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentServices as $service): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($service['plan_name']); ?></td>
                                                <td><?php echo htmlspecialchars($service['domain'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php
                                                        echo $service['status'] === 'active' ? 'success' :
                                                            ($service['status'] === 'pending' ? 'warning' : 'error');
                                                    ?>">
                                                        <?php echo htmlspecialchars($service['status']); ?>
                                                    </span>
                                                </td>
                                                <td>$<?php echo number_format($service['price'], 2); ?>/<?php echo $service['billing_cycle']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($service['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">
                                No services yet. <a href="plans.php" style="color: var(--primary-color);">Browse our plans</a> to get started.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Tickets</h2>
                        <a href="tickets.php" class="btn btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($recentTickets) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Department</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTickets as $ticket): ?>
                                            <tr>
                                                <td>
                                                    <a href="ticket-view.php?id=<?php echo $ticket['id']; ?>" style="color: var(--primary-color);">
                                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($ticket['department']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php
                                                        echo $ticket['priority'] === 'high' ? 'error' :
                                                            ($ticket['priority'] === 'medium' ? 'warning' : 'info');
                                                    ?>">
                                                        <?php echo htmlspecialchars($ticket['priority']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php
                                                        echo $ticket['status'] === 'open' ? 'success' :
                                                            ($ticket['status'] === 'pending' ? 'warning' : 'info');
                                                    ?>">
                                                        <?php echo htmlspecialchars($ticket['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">
                                No support tickets yet. <a href="tickets.php" style="color: var(--primary-color);">Create a ticket</a> if you need help.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>
