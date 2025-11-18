<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

// Get all customer invoices
$invoicesStmt = $db->prepare("
    SELECT i.*, s.domain, p.name as plan_name
    FROM invoices i
    LEFT JOIN services s ON i.service_id = s.id
    LEFT JOIN plans p ON s.plan_id = p.id
    WHERE i.customer_id = ?
    ORDER BY i.created_at DESC
");
$invoicesStmt->execute([$customerId]);
$invoices = $invoicesStmt->fetchAll();

// Calculate totals
$totalUnpaid = 0;
$totalPaid = 0;
foreach ($invoices as $invoice) {
    if ($invoice['status'] === 'unpaid') {
        $totalUnpaid += $invoice['amount'];
    } else {
        $totalPaid += $invoice['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .invoice-type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 5px;
            background: rgba(0, 212, 255, 0.2);
            color: var(--primary-color);
        }
        .payment-info {
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }
        .payment-info h4 {
            color: var(--primary-color);
            font-size: 14px;
            margin-bottom: 10px;
        }
        .payment-info pre {
            color: var(--text-primary);
            white-space: pre-wrap;
            font-family: inherit;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
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
                    <h1 class="page-title">Invoices</h1>
                    <p class="page-subtitle">View and manage your billing</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon red">
                            <span>💰</span>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($totalUnpaid, 2); ?></h3>
                            <p>Total Unpaid</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <span>✓</span>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($totalPaid, 2); ?></h3>
                            <p>Total Paid</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <span>📄</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($invoices); ?></h3>
                            <p>Total Invoices</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">All Invoices</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($invoices) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                            <th>Paid Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                                    <?php if ($invoice['invoice_type'] === 'manual'): ?>
                                                        <span class="invoice-type-badge">MANUAL</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($invoice['description']); ?><br>
                                                    <?php if ($invoice['plan_name']): ?>
                                                        <small style="color: var(--text-secondary);">
                                                            <?php echo htmlspecialchars($invoice['plan_name']); ?>
                                                            <?php if ($invoice['domain']): ?>
                                                                - <?php echo htmlspecialchars($invoice['domain']); ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    <?php endif; ?>

                                                    <?php if ($invoice['payment_link'] || $invoice['payment_details']): ?>
                                                        <div class="payment-info">
                                                            <?php if ($invoice['payment_link']): ?>
                                                                <h4>Payment Link:</h4>
                                                                <a href="<?php echo htmlspecialchars($invoice['payment_link']); ?>"
                                                                   target="_blank"
                                                                   class="btn btn-primary"
                                                                   style="padding: 8px 16px; font-size: 13px; display: inline-block; margin-bottom: 10px;">
                                                                    Pay via Custom Link
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if ($invoice['payment_details']): ?>
                                                                <h4>Payment Instructions:</h4>
                                                                <pre><?php echo htmlspecialchars($invoice['payment_details']); ?></pre>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong style="color: var(--primary-color);">
                                                        $<?php echo number_format($invoice['amount'], 2); ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php
                                                        echo $invoice['status'] === 'paid' ? 'success' :
                                                            ($invoice['status'] === 'pending' ? 'warning' : 'error');
                                                    ?>">
                                                        <?php echo htmlspecialchars($invoice['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $dueDate = strtotime($invoice['due_date']);
                                                    $isOverdue = $dueDate < time() && $invoice['status'] === 'unpaid';
                                                    ?>
                                                    <span style="color: <?php echo $isOverdue ? 'var(--error)' : 'var(--text-primary)'; ?>">
                                                        <?php echo date('M d, Y', $dueDate); ?>
                                                        <?php if ($isOverdue): ?>
                                                            <br><small>(Overdue)</small>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $invoice['paid_date'] ? date('M d, Y', strtotime($invoice['paid_date'])) : '-'; ?></td>
                                                <td>
                                                    <div style="display: flex; gap: 5px;">
                                                        <a href="invoice-view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                                            View
                                                        </a>
                                                        <?php if ($invoice['status'] === 'unpaid'): ?>
                                                            <?php if ($invoice['payment_link']): ?>
                                                                <a href="<?php echo htmlspecialchars($invoice['payment_link']); ?>"
                                                                   target="_blank"
                                                                   class="btn btn-success"
                                                                   style="padding: 6px 12px; font-size: 12px;">
                                                                    Pay Now
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="payment.php?invoice=<?php echo $invoice['id']; ?>" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">
                                                                    Pay Now
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-secondary); text-align: center; padding: 40px;">
                                No invoices yet.
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
