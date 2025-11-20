<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

$success = '';
$error = '';

// Get customer info
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = $_POST['full_name'] ?? '';
    $company = $_POST['company'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;

    try {
        $updateStmt = $db->prepare("
            UPDATE customers
            SET full_name = ?, company = ?, phone = ?, address = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$fullName, $company, $phone, $address, $customerId]);
        $success = 'Profile updated successfully!';

        // Refresh customer data
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Failed to update profile: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">My Profile</h1>
                    <p class="page-subtitle">Manage your account information</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Profile Information</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($customer['email']); ?>" disabled>
                                <small style="color: var(--text-secondary);">Email cannot be changed. Contact support to update.</small>
                            </div>

                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($customer['full_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="company">Company Name</label>
                                <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($customer['company'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>
