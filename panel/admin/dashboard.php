<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/cache-buster.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$cacheBusterVersion = getCacheBusterVersion();

// Get admin stats
$customersStmt = $db->query("SELECT COUNT(*) as count FROM customers WHERE is_admin = 0");
$customersCount = $customersStmt->fetch()['count'];

$servicesStmt = $db->query("SELECT COUNT(*) as count FROM services");
$servicesCount = $servicesStmt->fetch()['count'];

$activeServicesStmt = $db->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
$activeServicesCount = $activeServicesStmt->fetch()['count'];

$ticketsStmt = $db->query("SELECT COUNT(*) as count FROM tickets");
$ticketsCount = $ticketsStmt->fetch()['count'];

$openTicketsStmt = $db->query("SELECT COUNT(*) as count FROM tickets WHERE status = 'open'");
$openTicketsCount = $openTicketsStmt->fetch()['count'];

$invoicesStmt = $db->query("SELECT SUM(amount) as total FROM invoices WHERE status = 'unpaid'");
$unpaidTotal = $invoicesStmt->fetch()['total'] ?? 0;

$revenueStmt = $db->query("SELECT SUM(amount) as total FROM invoices WHERE status = 'paid'");
$totalRevenue = $revenueStmt->fetch()['total'] ?? 0;

// Recent customers
$recentCustomersStmt = $db->query("
    SELECT * FROM customers
    WHERE is_admin = 0
    ORDER BY created_at DESC
    LIMIT 5
");
$recentCustomers = $recentCustomersStmt->fetchAll();

// Recent tickets
$recentTicketsStmt = $db->query("
    SELECT t.*, c.full_name as customer_name, c.email as customer_email
    FROM tickets t
    JOIN customers c ON t.customer_id = c.id
    ORDER BY t.created_at DESC
    LIMIT 5
");
$recentTickets = $recentTicketsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShieldStack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Admin Dashboard</h1>
                    <p class="page-subtitle">System overview and management</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <span>👥</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $customersCount; ?></h3>
                            <p>Total Customers</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <span>🖥️</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $activeServicesCount; ?>/<?php echo $servicesCount; ?></h3>
                            <p>Active Services</p>
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
                            <span>💰</span>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="align-items: center; gap: 1rem;">
                        <div>
                            <h2 class="card-title" style="margin: 0;">Browser Cache Control</h2>
                            <p class="text-muted" style="margin: 0;">Force all users to fetch fresh CSS/JS on their next visit.</p>
                        </div>
                        <button class="btn btn-danger" id="clearCacheBtn">Clear Browser Cache</button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" id="cacheStatus">
                            Current cache version: <strong><?php echo htmlspecialchars($cacheBusterVersion); ?></strong>
                        </p>
                        <p style="margin-bottom: 0;">
                            Clearing cache bumps the global asset version. Browsers will auto-reload with new assets even if they previously cached old files.
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Customers</h2>
                        <a href="customers.php" class="btn btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($recentCustomers) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Company</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentCustomers as $customer): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['company'] ?? 'N/A'); ?></td>
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
                            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No customers yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Support Tickets</h2>
                        <a href="tickets.php" class="btn btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($recentTickets) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
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
                                                <td>#<?php echo $ticket['id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($ticket['customer_name']); ?><br>
                                                    <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($ticket['customer_email']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
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
                            <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No tickets yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        (function() {
            const btn = document.getElementById('clearCacheBtn');
            const statusEl = document.getElementById('cacheStatus');
            if (!btn || !statusEl) return;

            btn.addEventListener('click', async () => {
                btn.disabled = true;
                const original = btn.textContent;
                btn.textContent = 'Clearing...';

                try {
                    const res = await fetch('cache-bust.php', { method: 'POST' });
                    const data = await res.json();
                    if (!data.success) {
                        throw new Error(data.error || 'Request failed');
                    }
                    statusEl.innerHTML = 'Current cache version: <strong>' + data.version + '</strong>';
                    alert('Browser caches cleared. Users will fetch fresh assets on their next visit.');
                } catch (err) {
                    alert('Failed to clear cache: ' + err.message);
                } finally {
                    btn.disabled = false;
                    btn.textContent = original;
                }
            });
        })();
    </script>
</body>
</html>
