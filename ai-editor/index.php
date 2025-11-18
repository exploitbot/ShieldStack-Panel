<?php
require_once __DIR__ . '/../panel/includes/auth.php';
require_once __DIR__ . '/../panel/includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

// DEBUG: Show customer info
echo "<!-- DEBUG: Customer ID = " . htmlspecialchars($customerId ?? 'null') . " -->\n";
echo "<!-- DEBUG: Customer Email = " . htmlspecialchars($_SESSION['customer_email'] ?? 'not set') . " -->\n";

// Check if customer has an active AI plan
$stmt = $db->prepare("
    SELECT *
    FROM ai_service_plans
    WHERE customer_id = ? AND status = 'active'
    LIMIT 1
");
$stmt->execute([$customerId]);
$aiPlan = $stmt->fetch();

// DEBUG: Show AI plan result
echo "<!-- DEBUG: AI Plan found = " . ($aiPlan ? 'Yes' : 'No') . " -->\n";
if ($aiPlan) {
    echo "<!-- DEBUG: Plan type = " . htmlspecialchars($aiPlan['plan_type']) . ", Status = " . htmlspecialchars($aiPlan['status']) . " -->\n";
}

if (!$aiPlan) {
    // No active plan - show upgrade message
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AI Website Editor - ShieldStack</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <div class="dashboard-container">
            <?php include '../panel/includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include '../panel/includes/topbar.php'; ?>
                <div class="content-wrapper">
                    <div class="page-header">
                        <h1 class="page-title">🤖 AI Website Editor</h1>
                    </div>
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <h2>AI Website Editor Not Activated</h2>
                        <p class="text-muted">You don't have an active AI website editor plan.</p>
                        <p>Contact support to get started with AI-powered website editing!</p>
                        <a href="../tickets.php" class="btn btn-primary" style="margin-top: 1rem;">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Check if SSH credentials are configured
$stmt = $db->prepare("
    SELECT *
    FROM customer_ssh_credentials
    WHERE customer_id = ? AND is_active = 1
    LIMIT 1
");
$stmt->execute([$customerId]);
$sshCredentials = $stmt->fetch();

if (!$sshCredentials) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AI Website Editor - ShieldStack</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <div class="dashboard-container">
            <?php include '../panel/includes/sidebar.php'; ?>
            <div class="main-content">
                <?php include '../panel/includes/topbar.php'; ?>
                <div class="content-wrapper">
                    <div class="page-header">
                        <h1 class="page-title">🤖 AI Website Editor</h1>
                    </div>
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <h2>SSH Credentials Not Configured</h2>
                        <p class="text-muted">Your SSH credentials haven't been set up yet.</p>
                        <p>An administrator needs to configure your website's SSH access before you can use the AI editor.</p>
                        <a href="../tickets.php" class="btn btn-primary" style="margin-top: 1rem;">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Calculate token usage
$tokenPercent = $aiPlan['token_limit'] > 0 ?
    ($aiPlan['tokens_used'] / $aiPlan['token_limit']) * 100 : 0;

$tokensRemaining = $aiPlan['token_limit'] > 0 ?
    $aiPlan['token_limit'] - $aiPlan['tokens_used'] : -1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Website Editor - ShieldStack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/ai-editor.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../panel/includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../panel/includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">🤖 AI Website Editor</h1>
                    <p class="page-subtitle">Your AI-powered website assistant</p>
                </div>

                <!-- Token Usage Banner -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Plan:</strong> <?php echo ucfirst($aiPlan['plan_type']); ?>
                                <span style="margin: 0 1rem;">|</span>
                                <strong>Website:</strong> <?php echo htmlspecialchars($sshCredentials['website_name'] ?? $sshCredentials['website_url'] ?? 'Your Website'); ?>
                            </div>
                            <div>
                                <strong>Tokens:</strong>
                                <?php if ($aiPlan['token_limit'] == -1): ?>
                                    Unlimited
                                <?php else: ?>
                                    <?php echo number_format($aiPlan['tokens_used']); ?> / <?php echo number_format($aiPlan['token_limit']); ?>
                                    <small>(<?php echo number_format($tokensRemaining); ?> remaining)</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($aiPlan['token_limit'] > 0): ?>
                            <div class="progress" style="margin-top: 0.5rem;">
                                <div class="progress-bar progress-bar-<?php echo $tokenPercent > 90 ? 'danger' : ($tokenPercent > 70 ? 'warning' : 'success'); ?>"
                                     style="width: <?php echo min($tokenPercent, 100); ?>%">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="tabs">
                    <a href="index.php" class="tab active">💬 Chat</a>
                    <a href="usage.php" class="tab">📊 Usage</a>
                    <a href="history.php" class="tab">📝 History</a>
                </div>

                <!-- Chat Interface -->
                <div class="chat-container">
                    <div class="chat-messages" id="chatMessages">
                        <div class="message ai-message">
                            <div class="message-content">
                                <p><strong>AI Assistant:</strong> Hello! I'm your AI website editor. I have access to your website at <code><?php echo htmlspecialchars($sshCredentials['web_root_path']); ?></code>.</p>
                                <p>I can help you with:</p>
                                <ul>
                                    <li>Editing HTML, CSS, JavaScript, and PHP files</li>
                                    <li>Finding and modifying specific content</li>
                                    <li>Creating new pages or components</li>
                                    <li>Fixing bugs and errors</li>
                                    <li>Optimizing your code</li>
                                </ul>
                                <p>Just describe what you'd like to change, and I'll help you! I'll always create backups before making changes.</p>
                            </div>
                        </div>
                    </div>

                    <div class="chat-input-container">
                        <form id="chatForm" onsubmit="return false;">
                            <div class="chat-input-wrapper">
                                <textarea
                                    id="chatInput"
                                    placeholder="Describe the changes you'd like to make to your website..."
                                    rows="3"
                                ></textarea>
                                <button type="submit" id="sendButton" class="btn btn-primary">
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/chat-interface.js"></script>
    <script src="/panel/assets/js/mobile-menu.js"></script>
</body>
</html>
