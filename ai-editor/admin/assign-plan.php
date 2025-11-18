<?php
require_once __DIR__ . '/../../panel/includes/auth.php';
require_once __DIR__ . '/../../panel/includes/database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

$success = '';
$error = '';

// Define plan configurations
$planConfigs = [
    'basic' => ['tokens' => 10000, 'name' => 'AI Basic'],
    'pro' => ['tokens' => 50000, 'name' => 'AI Pro'],
    'enterprise' => ['tokens' => -1, 'name' => 'AI Enterprise']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'assign') {
            try {
                $customerId = $_POST['customer_id'];
                $planType = $_POST['plan_type'];
                $tokenLimit = $_POST['token_limit'];
                $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

                // Check if customer already has an active plan
                $stmt = $db->prepare("SELECT id FROM ai_service_plans WHERE customer_id = ? AND status = 'active'");
                $stmt->execute([$customerId]);

                if ($stmt->fetch()) {
                    $error = 'Customer already has an active AI plan. Please suspend it first.';
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO ai_service_plans
                        (customer_id, plan_type, token_limit, status, expires_at)
                        VALUES (?, ?, ?, 'active', ?)
                    ");
                    $stmt->execute([$customerId, $planType, $tokenLimit, $expiresAt]);
                    $success = 'AI plan assigned successfully!';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_status') {
            try {
                $planId = $_POST['plan_id'];
                $status = $_POST['status'];

                $stmt = $db->prepare("UPDATE ai_service_plans SET status = ? WHERE id = ?");
                $stmt->execute([$status, $planId]);
                $success = 'Plan status updated successfully!';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'add_tokens') {
            try {
                $planId = $_POST['plan_id'];
                $tokensToAdd = intval($_POST['tokens_to_add']);

                $stmt = $db->prepare("
                    UPDATE ai_service_plans
                    SET token_limit = token_limit + ?
                    WHERE id = ?
                ");
                $stmt->execute([$tokensToAdd, $planId]);
                $success = "Added {$tokensToAdd} tokens successfully!";
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'reset_tokens') {
            try {
                $planId = $_POST['plan_id'];

                $stmt = $db->prepare("UPDATE ai_service_plans SET tokens_used = 0 WHERE id = ?");
                $stmt->execute([$planId]);
                $success = 'Token usage reset successfully!';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}

// Get customers without active plans
$availableCustomers = $db->query("
    SELECT c.id, c.full_name, c.email
    FROM customers c
    LEFT JOIN ai_service_plans p ON c.id = p.customer_id AND p.status = 'active'
    WHERE p.id IS NULL
    ORDER BY c.full_name
")->fetchAll();

// Get all active plans
$activePlans = $db->query("
    SELECT
        p.*,
        c.full_name,
        c.email
    FROM ai_service_plans p
    JOIN customers c ON p.customer_id = c.id
    ORDER BY p.activated_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign AI Plan - Admin</title>
    <link rel="stylesheet" href="/panel/assets/css/style.css">
    <script>
        function updateTokenLimit() {
            const planType = document.getElementById('plan_type').value;
            const tokenLimitInput = document.getElementById('token_limit');

            const limits = {
                'basic': 10000,
                'pro': 50000,
                'enterprise': -1
            };

            tokenLimitInput.value = limits[planType];
        }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../panel/admin/includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../../panel/admin/includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">📦 Assign AI Plans</h1>
                    <p class="page-subtitle">Assign AI website editor plans to customers</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Assign New Plan -->
                <div class="card">
                    <div class="card-header">
                        <h3>Assign New AI Plan</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($availableCustomers)): ?>
                            <p class="text-muted">All customers already have active AI plans.</p>
                        <?php else: ?>
                            <form method="POST" class="form">
                                <input type="hidden" name="action" value="assign">

                                <div class="form-group">
                                    <label>Customer *</label>
                                    <select name="customer_id" required class="form-control">
                                        <option value="">Select customer...</option>
                                        <?php foreach ($availableCustomers as $customer): ?>
                                            <option value="<?php echo $customer['id']; ?>">
                                                <?php echo htmlspecialchars($customer['full_name'] . ' (' . $customer['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Plan Type *</label>
                                        <select name="plan_type" id="plan_type" required class="form-control" onchange="updateTokenLimit()">
                                            <option value="">Select plan...</option>
                                            <option value="basic">Basic (10,000 tokens/month)</option>
                                            <option value="pro">Pro (50,000 tokens/month)</option>
                                            <option value="enterprise">Enterprise (Unlimited)</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Token Limit *</label>
                                        <input type="number" name="token_limit" id="token_limit" required class="form-control" placeholder="10000">
                                        <small class="form-text text-muted">Use -1 for unlimited</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Expiration Date (Optional)</label>
                                    <input type="datetime-local" name="expires_at" class="form-control">
                                    <small class="form-text text-muted">Leave empty for no expiration</small>
                                </div>

                                <button type="submit" class="btn btn-primary">Assign Plan</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Existing Plans -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Existing AI Plans</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activePlans)): ?>
                            <p class="text-muted">No AI plans assigned yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Plan Type</th>
                                            <th>Tokens</th>
                                            <th>Usage</th>
                                            <th>Status</th>
                                            <th>Activated</th>
                                            <th>Expires</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activePlans as $plan): ?>
                                            <?php
                                                $tokenPercent = $plan['token_limit'] > 0 ?
                                                    ($plan['tokens_used'] / $plan['token_limit']) * 100 : 0;

                                                $progressClass = 'success';
                                                if ($tokenPercent > 90) $progressClass = 'danger';
                                                elseif ($tokenPercent > 70) $progressClass = 'warning';
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($plan['full_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($plan['email']); ?></small>
                                                </td>
                                                <td><span class="badge badge-<?php echo $plan['plan_type']; ?>"><?php echo ucfirst($plan['plan_type']); ?></span></td>
                                                <td>
                                                    <?php echo number_format($plan['tokens_used']); ?> /
                                                    <?php echo $plan['token_limit'] == -1 ? '∞' : number_format($plan['token_limit']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($plan['token_limit'] > 0): ?>
                                                        <div class="progress">
                                                            <div class="progress-bar progress-bar-<?php echo $progressClass; ?>"
                                                                 style="width: <?php echo min($tokenPercent, 100); ?>%">
                                                                <?php echo round($tokenPercent, 1); ?>%
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Unlimited</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $plan['status']; ?>">
                                                        <?php echo ucfirst($plan['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($plan['activated_at'])); ?></td>
                                                <td>
                                                    <?php if ($plan['expires_at']): ?>
                                                        <?php echo date('M d, Y', strtotime($plan['expires_at'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Never</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <!-- Status Toggle -->
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                                        <?php if ($plan['status'] === 'active'): ?>
                                                            <input type="hidden" name="status" value="suspended">
                                                            <button type="submit" class="btn btn-sm btn-warning">Suspend</button>
                                                        <?php else: ?>
                                                            <input type="hidden" name="status" value="active">
                                                            <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                                        <?php endif; ?>
                                                    </form>

                                                    <!-- Reset Tokens -->
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Reset token usage to 0?');">
                                                        <input type="hidden" name="action" value="reset_tokens">
                                                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-secondary">Reset</button>
                                                    </form>

                                                    <!-- Add Tokens Button (triggers modal) -->
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="showAddTokensModal(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars($plan['full_name']); ?>')">
                                                        Add Tokens
                                                    </button>
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
    </div>

    <!-- Add Tokens Modal -->
    <div id="addTokensModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
            <h3>Add Tokens</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_tokens">
                <input type="hidden" name="plan_id" id="modal_plan_id">

                <div class="form-group">
                    <label>Customer: <strong id="modal_customer_name"></strong></label>
                </div>

                <div class="form-group">
                    <label>Tokens to Add</label>
                    <input type="number" name="tokens_to_add" class="form-control" min="1" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Add Tokens</button>
                    <button type="button" class="btn btn-secondary" onclick="hideAddTokensModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddTokensModal(planId, customerName) {
            document.getElementById('modal_plan_id').value = planId;
            document.getElementById('modal_customer_name').textContent = customerName;
            document.getElementById('addTokensModal').style.display = 'block';
        }

        function hideAddTokensModal() {
            document.getElementById('addTokensModal').style.display = 'none';
        }
    </script>
    <script src="/panel/assets/js/mobile-menu.js"></script>
</body>
</html>
