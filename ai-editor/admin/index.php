<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

// Get statistics
$statsQuery = "
    SELECT
        (SELECT COUNT(*) FROM ai_service_plans WHERE status = 'active') as active_plans,
        (SELECT COUNT(*) FROM customer_ssh_credentials WHERE is_active = 1) as active_ssh,
        (SELECT COUNT(*) FROM ai_chat_sessions WHERE is_active = 1) as active_sessions,
        (SELECT COUNT(*) FROM ai_change_logs WHERE executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as changes_24h,
        (SELECT SUM(tokens_used) FROM ai_service_plans) as total_tokens_used,
        (SELECT COUNT(*) FROM ai_change_logs WHERE success = 0 AND executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as errors_24h
";

$stats = $db->query($statsQuery)->fetch();

// Get recent activity
$recentActivity = $db->query("
    SELECT
        c.customer_id,
        cu.full_name,
        cu.email,
        c.user_request,
        c.success,
        c.tokens_used,
        c.executed_at
    FROM ai_change_logs c
    JOIN customers cu ON c.customer_id = cu.id
    ORDER BY c.executed_at DESC
    LIMIT 10
")->fetchAll();

// Get customers with AI plans
$customersWithPlans = $db->query("
    SELECT
        cu.id,
        cu.full_name,
        cu.email,
        p.plan_type,
        p.token_limit,
        p.tokens_used,
        p.status,
        p.activated_at
    FROM customers cu
    JOIN ai_service_plans p ON cu.id = p.customer_id
    WHERE p.status = 'active'
    ORDER BY p.activated_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Editor Admin - ShieldStack</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/ai-editor.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../admin/includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../../admin/includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">🤖 AI Website Editor Administration</h1>
                    <p class="page-subtitle">Manage AI plans, credentials, and monitor activity</p>
                </div>

                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <span>📦</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['active_plans']; ?></h3>
                            <p>Active AI Plans</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <span>🔐</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['active_ssh']; ?></h3>
                            <p>SSH Connections</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <span>💬</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['active_sessions']; ?></h3>
                            <p>Active Chat Sessions</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <span>⚡</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['changes_24h']; ?></h3>
                            <p>Changes (24h)</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon cyan">
                            <span>🎯</span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_tokens_used']); ?></h3>
                            <p>Total Tokens Used</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon <?php echo $stats['errors_24h'] > 0 ? 'red' : 'green'; ?>">
                            <span><?php echo $stats['errors_24h'] > 0 ? '⚠️' : '✅'; ?></span>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['errors_24h']; ?></h3>
                            <p>Errors (24h)</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="button-group">
                            <a href="assign-plan.php" class="btn btn-primary">➕ Assign AI Plan</a>
                            <a href="manage-ssh.php" class="btn btn-secondary">🔐 Manage SSH Credentials</a>
                            <a href="view-logs.php" class="btn btn-secondary">📊 View All Logs</a>
                            <a href="settings.php" class="btn btn-secondary">⚙️ AI Settings</a>
                        </div>
                    </div>
                </div>

                <!-- Customers with AI Plans -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Active AI Plans</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($customersWithPlans)): ?>
                            <p class="text-muted">No active AI plans yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Email</th>
                                            <th>Plan Type</th>
                                            <th>Tokens Used</th>
                                            <th>Status</th>
                                            <th>Activated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customersWithPlans as $customer): ?>
                                            <?php
                                                $tokenPercent = $customer['token_limit'] > 0 ?
                                                    ($customer['tokens_used'] / $customer['token_limit']) * 100 : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                <td><span class="badge badge-<?php echo $customer['plan_type']; ?>"><?php echo ucfirst($customer['plan_type']); ?></span></td>
                                                <td>
                                                    <?php echo number_format($customer['tokens_used']); ?> /
                                                    <?php echo $customer['token_limit'] == -1 ? '∞' : number_format($customer['token_limit']); ?>
                                                    <?php if ($customer['token_limit'] > 0): ?>
                                                        <small>(<?php echo round($tokenPercent, 1); ?>%)</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge badge-<?php echo $customer['status']; ?>"><?php echo ucfirst($customer['status']); ?></span></td>
                                                <td><?php echo date('M d, Y', strtotime($customer['activated_at'])); ?></td>
                                                <td>
                                                    <a href="edit-plan.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentActivity)): ?>
                            <p class="text-muted">No activity yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Request</th>
                                            <th>Tokens</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivity as $activity): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($activity['full_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($activity['email']); ?></small>
                                                </td>
                                                <td>
                                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?php echo htmlspecialchars($activity['user_request']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($activity['tokens_used']); ?></td>
                                                <td>
                                                    <?php if ($activity['success']): ?>
                                                        <span class="badge badge-success">✓ Success</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">✗ Failed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M d, Y H:i', strtotime($activity['executed_at'])); ?></small>
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
</body>
</html>
