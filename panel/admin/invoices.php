<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/email.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

$success = '';
$error = '';

// Handle mark as paid
if (isset($_GET['mark_paid']) && is_numeric($_GET['mark_paid'])) {
    $invoiceId = $_GET['mark_paid'];
    $stmt = $db->prepare("UPDATE invoices SET status = 'paid', paid_date = NOW() WHERE id = ?");
    $stmt->execute([$invoiceId]);

    // Send email notification
    $emailService = new EmailService();
    $emailService->sendInvoicePaidNotification($invoiceId);

    $success = 'Invoice marked as paid successfully!';
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $invoiceId = $_GET['delete'];
    // Only allow deleting manual invoices
    $checkStmt = $db->prepare("SELECT invoice_type FROM invoices WHERE id = ?");
    $checkStmt->execute([$invoiceId]);
    $invoice = $checkStmt->fetch();
    
    if ($invoice && $invoice['invoice_type'] === 'manual') {
        $deleteStmt = $db->prepare("DELETE FROM invoices WHERE id = ?");
        $deleteStmt->execute([$invoiceId]);
        $success = 'Invoice deleted successfully!';
    } else {
        $error = 'Only manual invoices can be deleted!';
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$customerFilter = $_GET['customer'] ?? '';

// Build query
$query = "
    SELECT i.*, c.full_name as customer_name, c.email as customer_email,
           s.domain, p.name as plan_name
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    LEFT JOIN services s ON i.service_id = s.id
    LEFT JOIN plans p ON s.plan_id = p.id
    WHERE 1=1
";

$params = [];

if ($statusFilter) {
    $query .= " AND i.status = ?";
    $params[] = $statusFilter;
}

if ($typeFilter) {
    $query .= " AND i.invoice_type = ?";
    $params[] = $typeFilter;
}

if ($customerFilter) {
    $query .= " AND i.customer_id = ?";
    $params[] = $customerFilter;
}

$query .= " ORDER BY i.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$invoices = $stmt->fetchAll();

// Get all customers for filter
$customersStmt = $db->query("SELECT id, full_name FROM customers WHERE is_admin = 0 ORDER BY full_name");
$customers = $customersStmt->fetchAll();

// Calculate stats
$totalUnpaid = 0;
$totalPaid = 0;
$totalManual = 0;

foreach ($invoices as $invoice) {
    if ($invoice['status'] === 'unpaid') {
        $totalUnpaid += $invoice['amount'];
    } elseif ($invoice['status'] === 'paid') {
        $totalPaid += $invoice['amount'];
    }
    if ($invoice['invoice_type'] === 'manual') {
        $totalManual++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Invoices - ShieldStack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filters {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .invoice-type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 5px;
        }
        .invoice-type-badge.manual {
            background: rgba(0, 212, 255, 0.2);
            color: var(--primary-color);
        }
        .invoice-type-badge.auto {
            background: rgba(255, 170, 0, 0.1);
            color: var(--warning);
        }
        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
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
                    <h1 class="page-title">Manage Invoices</h1>
                    <p class="page-subtitle">View and manage all customer invoices</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

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

                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <span>✏️</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $totalManual; ?></h3>
                            <p>Manual Invoices</p>
                        </div>
                    </div>
                </div>

                <div class="filters">
                    <form method="GET" action="">
                        <div class="filter-row">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="status">Status</label>
                                <select name="status" id="status">
                                    <option value="">All Statuses</option>
                                    <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="unpaid" <?php echo $statusFilter === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="type">Type</label>
                                <select name="type" id="type">
                                    <option value="">All Types</option>
                                    <option value="manual" <?php echo $typeFilter === 'manual' ? 'selected' : ''; ?>>Manual</option>
                                    <option value="auto" <?php echo $typeFilter === 'auto' ? 'selected' : ''; ?>>Automatic</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="customer">Customer</label>
                                <select name="customer" id="customer">
                                    <option value="">All Customers</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>" <?php echo $customerFilter == $customer['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($customer['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="invoices.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">All Invoices</h2>
                        <a href="create-invoice.php" class="btn btn-primary">Create Invoice</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($invoices) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Customer</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($invoice['customer_name']); ?><br>
                                                    <small style="color: var(--text-secondary);">
                                                        <?php echo htmlspecialchars($invoice['customer_email']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($invoice['description']); ?>
                                                    <?php if ($invoice['payment_link']): ?>
                                                        <br><small style="color: var(--primary-color);">🔗 Has Payment Link</small>
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
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="invoice-type-badge <?php echo $invoice['invoice_type']; ?>">
                                                        <?php echo htmlspecialchars($invoice['invoice_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php if ($invoice['status'] === 'unpaid'): ?>
                                                            <a href="?mark_paid=<?php echo $invoice['id']; ?>" 
                                                               class="btn btn-success"
                                                               onclick="return confirm('Mark this invoice as paid?');">
                                                                Mark Paid
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($invoice['invoice_type'] === 'manual'): ?>
                                                            <a href="?delete=<?php echo $invoice['id']; ?>" 
                                                               class="btn btn-danger"
                                                               onclick="return confirm('Delete this manual invoice? This cannot be undone!');">
                                                                Delete
                                                            </a>
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
                                No invoices found. <a href="create-invoice.php" style="color: var(--primary-color);">Create one now</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
