<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

// Get AI plan
$stmt = $db->prepare("SELECT * FROM ai_service_plans WHERE customer_id = ? AND status = 'active' LIMIT 1");
$stmt->execute([$customerId]);
$aiPlan = $stmt->fetch();

if (!$aiPlan) {
    header('Location: index.php');
    exit;
}

// Calculate stats
$tokenPercent = $aiPlan['token_limit'] > 0 ?
    ($aiPlan['tokens_used'] / $aiPlan['token_limit']) * 100 : 0;

$tokensRemaining = $aiPlan['token_limit'] > 0 ?
    $aiPlan['token_limit'] - $aiPlan['tokens_used'] : -1;

// Get usage by day (last 30 days)
$usageByDay = $db->prepare("
    SELECT
        DATE(executed_at) as date,
        SUM(tokens_used) as tokens,
        COUNT(*) as requests
    FROM ai_change_logs
    WHERE customer_id = ?
    AND executed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(executed_at)
    ORDER BY date DESC
");
$usageByDay->execute([$customerId]);
$dailyUsage = $usageByDay->fetchAll();

// Get total stats
$totalStats = $db->prepare("
    SELECT
        COUNT(*) as total_requests,
        SUM(tokens_used) as total_tokens,
        COUNT(CASE WHEN success = 1 THEN 1 END) as successful_requests,
        COUNT(CASE WHEN success = 0 THEN 1 END) as failed_requests
    FROM ai_change_logs
    WHERE customer_id = ?
");
$totalStats->execute([$customerId]);
$stats = $totalStats->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Usage - AI Editor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/ai-editor.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">📊 Token Usage</h1>
                    <p class="page-subtitle">Monitor your AI token consumption</p>
                </div>

                <!-- Navigation Tabs -->
                <div class="tabs">
                    <a href="index.php" class="tab">💬 Chat</a>
                    <a href="usage.php" class="tab active">📊 Usage</a>
                    <a href="history.php" class="tab">📝 History</a>
                </div>

                <!-- Current Plan -->
                <div class="card">
                    <div class="card-header">
                        <h3>Current Plan: <?php echo ucfirst($aiPlan['plan_type']); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon blue">
                                    <span>🎯</span>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo number_format($aiPlan['tokens_used']); ?></h3>
                                    <p>Tokens Used</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon green">
                                    <span>💎</span>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $aiPlan['token_limit'] == -1 ? '∞' : number_format($tokensRemaining); ?></h3>
                                    <p>Tokens Remaining</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon purple">
                                    <span>📈</span>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $aiPlan['token_limit'] == -1 ? 'Unlimited' : round($tokenPercent, 1) . '%'; ?></h3>
                                    <p>Usage</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon orange">
                                    <span>📦</span>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $aiPlan['token_limit'] == -1 ? '∞' : number_format($aiPlan['token_limit']); ?></h3>
                                    <p>Total Limit</p>
                                </div>
                            </div>
                        </div>

                        <?php if ($aiPlan['token_limit'] > 0): ?>
                            <div style="margin-top: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Token Usage</span>
                                    <span><?php echo round($tokenPercent, 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-<?php echo $tokenPercent > 90 ? 'danger' : ($tokenPercent > 70 ? 'warning' : 'success'); ?>"
                                         style="width: <?php echo min($tokenPercent, 100); ?>%">
                                    </div>
                                </div>

                                <?php if ($tokenPercent > 80): ?>
                                    <div class="alert alert-warning" style="margin-top: 1rem;">
                                        You're running low on tokens! Contact support to add more tokens to your plan.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Overall Statistics -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Overall Statistics</h3>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-info">
                                    <h3><?php echo number_format($stats['total_requests']); ?></h3>
                                    <p>Total Requests</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-info">
                                    <h3><?php echo number_format($stats['total_tokens']); ?></h3>
                                    <p>Total Tokens Used</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-info">
                                    <h3><?php echo number_format($stats['successful_requests']); ?></h3>
                                    <p>Successful</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-info">
                                    <h3><?php echo number_format($stats['failed_requests']); ?></h3>
                                    <p>Failed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Usage -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Daily Usage (Last 30 Days)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dailyUsage)): ?>
                            <p class="text-muted">No usage data yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Requests</th>
                                            <th>Tokens Used</th>
                                            <th>Avg Tokens/Request</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dailyUsage as $day): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                                <td><?php echo number_format($day['requests']); ?></td>
                                                <td><?php echo number_format($day['tokens']); ?></td>
                                                <td><?php echo number_format($day['tokens'] / $day['requests']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Plan Details -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Plan Details</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <td><strong>Plan Type:</strong></td>
                                <td><span class="badge badge-<?php echo $aiPlan['plan_type']; ?>"><?php echo ucfirst($aiPlan['plan_type']); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-<?php echo $aiPlan['status']; ?>"><?php echo ucfirst($aiPlan['status']); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Activated:</strong></td>
                                <td><?php echo date('M d, Y', strtotime($aiPlan['activated_at'])); ?></td>
                            </tr>
                            <?php if ($aiPlan['expires_at']): ?>
                                <tr>
                                    <td><strong>Expires:</strong></td>
                                    <td><?php echo date('M d, Y', strtotime($aiPlan['expires_at'])); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
