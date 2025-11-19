<?php
require_once __DIR__ . '/../panel/includes/auth.php';
require_once __DIR__ . '/../panel/includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

// Handle website selection before rendering chat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_website_id'])) {
    $selectedWebsiteId = (int)$_POST['selected_website_id'];
    $_SESSION['ai_editor_selected_website_id'] = $selectedWebsiteId;
    header('Location: index.php');
    exit;
}

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

// Fetch active websites for the customer
$websitesStmt = $db->prepare("
    SELECT *
    FROM customer_ssh_credentials
    WHERE customer_id = ? AND is_active = 1
    ORDER BY website_name IS NULL, website_name, id
");
$websitesStmt->execute([$customerId]);
$websites = $websitesStmt->fetchAll();

if (empty($websites)) {
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
                        <h2>No Websites Assigned</h2>
                        <p class="text-muted">You need at least one active website before using the AI editor.</p>
                        <p>Please ask an admin to add SSH credentials for your websites.</p>
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

// Determine selected website
$selectedWebsiteId = $_SESSION['ai_editor_selected_website_id'] ?? null;
if (isset($_GET['website_id'])) {
    $selectedWebsiteId = (int)$_GET['website_id'];
    $_SESSION['ai_editor_selected_website_id'] = $selectedWebsiteId;
}

$selectedWebsite = null;
foreach ($websites as $site) {
    if ((int)$site['id'] === (int)$selectedWebsiteId) {
        $selectedWebsite = $site;
        break;
    }
}

if (!$selectedWebsite) {
    $selectedWebsite = $websites[0];
    $selectedWebsiteId = $selectedWebsite['id'];
    $_SESSION['ai_editor_selected_website_id'] = $selectedWebsiteId;
}

$sshCredentials = $selectedWebsite;

// Calculate token usage
$tokenPercent = $aiPlan['token_limit'] > 0 ?
    ($aiPlan['tokens_used'] / $aiPlan['token_limit']) * 100 : 0;

$tokensRemaining = $aiPlan['token_limit'] > 0 ?
    $aiPlan['token_limit'] - $aiPlan['tokens_used'] : -1;

$selectedWebsiteName = $sshCredentials['website_name'] ?? $sshCredentials['website_url'] ?? 'Your Website';
$websitesForJs = array_map(function ($site) {
    return [
        'id' => (int)$site['id'],
        'name' => $site['website_name'] ?? ($site['website_url'] ?? 'Website'),
        'url' => $site['website_url'] ?? '',
        'web_root_path' => $site['web_root_path'] ?? '',
        'type' => $site['website_type'] ?? 'custom'
    ];
}, $websites);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Website Editor - ShieldStack</title>
    <link rel="apple-touch-icon" sizes="512x512" href="/upload/uploads/temp/B2-QCJKt.png">
    <link rel="manifest" href="/ai-editor/manifest.webmanifest">
    <meta name="theme-color" content="#050816">
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
                    <div>
                        <h1 class="page-title">🤖 AI Website Editor</h1>
                        <p class="page-subtitle">Your AI-powered website assistant</p>
                    </div>
                    <div class="website-pill">
                        <span class="label">Editing</span>
                        <span class="value"><?php echo htmlspecialchars($selectedWebsiteName); ?></span>
                    </div>
                </div>

                <div class="card website-picker-card">
                    <div class="card-body">
                        <form method="POST" class="website-picker-form">
                            <div>
                                <label for="selected_website_id"><strong>Select website to edit</strong></label>
                                <p class="text-muted" style="margin: 0;">Choose a website before opening chat sessions.</p>
                            </div>
                            <div class="website-picker-controls">
                                <select name="selected_website_id" id="selected_website_id" required>
                                    <?php foreach ($websites as $site): ?>
                                        <option value="<?php echo $site['id']; ?>" <?php echo ((int)$site['id'] === (int)$selectedWebsiteId) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($site['website_name'] ?? ($site['website_url'] ?? 'Website #' . $site['id'])); ?>
                                            (<?php echo htmlspecialchars($site['web_root_path']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">Switch Website</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Token Usage Banner -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            <div>
                                <strong>Plan:</strong> <?php echo ucfirst($aiPlan['plan_type']); ?>
                                <span style="margin: 0 1rem;">|</span>
                                <strong>Website:</strong> <?php echo htmlspecialchars($selectedWebsiteName); ?>
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

                <div class="chat-layout">
                    <aside class="session-panel card">
                        <div class="card-header">
                            <div>
                                <h3 style="margin: 0;">Chat Sessions</h3>
                                <small class="text-muted">Open multiple sessions per website</small>
                            </div>
                            <div class="session-panel-actions">
                                <button class="btn btn-primary btn-sm" id="newSessionBtn">+ New Session</button>
                                <button class="btn btn-secondary btn-sm" id="refreshSessionsBtn">Refresh</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="sessionList" class="session-list">
                                <p class="text-muted" style="margin: 0;">Loading sessions...</p>
                            </div>
                        </div>
                        <div class="card-footer session-footer">
                            <button class="btn btn-danger btn-sm" id="clearSessionBtn">Clear Active Session</button>
                            <span class="text-muted" id="sessionStatus"></span>
                        </div>
                    </aside>

                    <div class="chat-area">
                        <div class="open-session-tabs" id="openSessionsTabs"></div>

                        <!-- Chat Interface -->
                        <div class="chat-container">
                            <div class="chat-messages" id="chatMessages">
                                <div class="message ai-message">
                                    <div class="message-content">
                                        <p><strong>AI Assistant:</strong> Hello! I'm your AI website editor for <code><?php echo htmlspecialchars($sshCredentials['web_root_path']); ?></code>.</p>
                                        <p>I can help you with:</p>
                                        <ul>
                                            <li>Editing HTML, CSS, JavaScript, and PHP files</li>
                                            <li>Finding and modifying specific content</li>
                                            <li>Creating new pages or components</li>
                                            <li>Fixing bugs and errors</li>
                                            <li>Optimizing your code</li>
                                        </ul>
                                        <p>Pick a chat session on the left (or start a new one) and tell me what to change. I always back up files before modifying them.</p>
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
                                <div class="chat-actions-row">
                                    <button class="btn btn-secondary btn-sm" id="newSessionInlineBtn">Start New Session</button>
                                    <span class="text-muted" id="activeSessionLabel"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.aiEditorConfig = {
            selectedWebsiteId: <?php echo (int)$sshCredentials['id']; ?>,
            selectedWebsiteName: <?php echo json_encode($selectedWebsiteName); ?>,
            websites: <?php echo json_encode($websitesForJs); ?>,
            endpoints: {
                chat: 'api/chat.php',
                getSession: 'api/get-session.php',
                sessions: 'api/sessions.php'
            }
        };
    </script>
    <script src="assets/js/chat-interface.js"></script>
    <script src="/panel/assets/js/mobile-menu.js"></script>
</body>
</html>
