<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/email.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $status = $_POST['status'] ?? 'unpaid';
    $payment_link = $_POST['payment_link'] ?? '';
    $payment_details = $_POST['payment_details'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $send_email = isset($_POST['send_email']);

    // Validation
    if (empty($customer_id)) {
        $error = 'Please select a customer';
    } elseif (empty($amount) || $amount <= 0) {
        $error = 'Please enter a valid amount greater than 0';
    } elseif (empty($description)) {
        $error = 'Please enter a description';
    } elseif (empty($due_date)) {
        $error = 'Please select a due date';
    } else {
        try {
            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // Insert invoice
            $stmt = $db->prepare("
                INSERT INTO invoices (
                    customer_id, invoice_number, amount, description,
                    status, due_date, payment_link, payment_details,
                    invoice_type, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'manual', ?, NOW())
            ");

            $stmt->execute([
                $customer_id,
                $invoiceNumber,
                $amount,
                $description,
                $status,
                $due_date,
                $payment_link,
                $payment_details,
                $notes
            ]);

            $invoiceId = $db->lastInsertId();

            // Update paid_date if status is paid
            if ($status === 'paid') {
                $updateStmt = $db->prepare("UPDATE invoices SET paid_date = NOW() WHERE id = ?");
                $updateStmt->execute([$invoiceId]);
            }

            // Send email notification
            $emailService = new EmailService();
            $emailService->sendInvoiceCreatedNotification($invoiceId);

            $success = "Invoice $invoiceNumber created successfully!";

            // Clear form
            $_POST = [];

        } catch (Exception $e) {
            $error = 'Failed to create invoice: ' . $e->getMessage();
        }
    }
}

// Get all customers (non-admin)
$customersStmt = $db->query("SELECT id, full_name, email FROM customers WHERE is_admin = 0 ORDER BY full_name");
$customers = $customersStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice - ShieldStack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-actions {
                flex-direction: column;
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
                    <h1 class="page-title">Create Manual Invoice</h1>
                    <p class="page-subtitle">Create a custom invoice for a customer</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Invoice Details</h2>
                        <a href="invoices.php" class="btn btn-secondary">Back to Invoices</a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="customer_id">Customer *</label>
                                    <select name="customer_id" id="customer_id" required>
                                        <option value="">Select Customer</option>
                                        <?php foreach ($customers as $customer): ?>
                                            <option value="<?php echo $customer['id']; ?>" <?php echo (isset($_POST['customer_id']) && $_POST['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($customer['full_name'] . ' (' . $customer['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="amount">Amount (USD) *</label>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                           value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>"
                                           placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="due_date">Due Date *</label>
                                    <input type="date" name="due_date" id="due_date"
                                           value="<?php echo htmlspecialchars($_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days'))); ?>"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select name="status" id="status" required>
                                        <option value="unpaid" <?php echo (isset($_POST['status']) && $_POST['status'] == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="paid" <?php echo (isset($_POST['status']) && $_POST['status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="cancelled" <?php echo (isset($_POST['status']) && $_POST['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea name="description" id="description" rows="3" required
                                          placeholder="e.g., Custom Development Work, Consulting Services, etc."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="payment_link">Payment Link (Optional)</label>
                                <input type="url" name="payment_link" id="payment_link"
                                       value="<?php echo htmlspecialchars($_POST['payment_link'] ?? ''); ?>"
                                       placeholder="https://payment.example.com/invoice/...">
                                <small style="color: var(--text-secondary); display: block; margin-top: 5px;">
                                    Provide a custom payment URL (e.g., PayPal, Stripe, etc.)
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="payment_details">Payment Instructions (Optional)</label>
                                <textarea name="payment_details" id="payment_details" rows="4"
                                          placeholder="e.g., Wire transfer details, PayPal email, cryptocurrency address, etc."><?php echo htmlspecialchars($_POST['payment_details'] ?? ''); ?></textarea>
                                <small style="color: var(--text-secondary); display: block; margin-top: 5px;">
                                    These instructions will be visible to the customer
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="notes">Admin Notes (Optional)</label>
                                <textarea name="notes" id="notes" rows="3"
                                          placeholder="Internal notes not visible to customer..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                                <small style="color: var(--text-secondary); display: block; margin-top: 5px;">
                                    These notes are for internal use only and will not be shown to the customer
                                </small>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="send_email" value="1" <?php echo isset($_POST['send_email']) ? 'checked' : ''; ?>>
                                    <span>Send email notification to customer</span>
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Create Invoice</button>
                                <a href="invoices.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
