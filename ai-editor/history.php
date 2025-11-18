<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM ai_change_logs WHERE customer_id = ?");
$countStmt->execute([$customerId]);
$totalRecords = $countStmt->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get change logs
$stmt = $db->prepare("
    SELECT
        c.*,
        s.website_name,
        s.website_url
    FROM ai_change_logs c
    LEFT JOIN customer_ssh_credentials s ON c.ssh_credential_id = s.id
    WHERE c.customer_id = ?
    ORDER BY c.executed_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$customerId, $perPage, $offset]);
$changes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change History - AI Editor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/ai-editor.css">
    <style>
        .change-details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 0.5rem;
            display: none;
        }
        .change-details.show {
            display: block;
        }
        .files-modified {
            margin-top: 0.5rem;
        }
        .file-badge {
            display: inline-block;
            background: #e0e0e0;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin: 0.25rem;
            font-family: monospace;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">📝 Change History</h1>
                    <p class="page-subtitle">View all AI-executed changes to your website</p>
                </div>

                <!-- Navigation Tabs -->
                <div class="tabs">
                    <a href="index.php" class="tab">💬 Chat</a>
                    <a href="usage.php" class="tab">📊 Usage</a>
                    <a href="history.php" class="tab active">📝 History</a>
                </div>

                <!-- Change History -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Changes (<?php echo number_format($totalRecords); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($changes)): ?>
                            <p class="text-muted">No changes yet. Start chatting with the AI to make website modifications!</p>
                        <?php else: ?>
                            <?php foreach ($changes as $change): ?>
                                <div class="card" style="margin-bottom: 1rem;">
                                    <div class="card-body">
                                        <div style="display: flex; justify-content: space-between; align-items: start;">
                                            <div style="flex: 1;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <?php if ($change['success']): ?>
                                                        <span class="badge badge-success">✓ Success</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">✗ Failed</span>
                                                    <?php endif; ?>

                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y H:i:s', strtotime($change['executed_at'])); ?>
                                                    </small>

                                                    <?php if ($change['website_name']): ?>
                                                        <small class="text-muted">
                                                            • <?php echo htmlspecialchars($change['website_name']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>

                                                <div style="margin-top: 0.5rem;">
                                                    <strong>Request:</strong>
                                                    <p style="margin: 0.25rem 0;">
                                                        <?php echo htmlspecialchars($change['user_request']); ?>
                                                    </p>
                                                </div>

                                                <?php if ($change['files_modified']): ?>
                                                    <?php $files = json_decode($change['files_modified'], true); ?>
                                                    <?php if (!empty($files)): ?>
                                                        <div class="files-modified">
                                                            <strong>Files Modified:</strong><br>
                                                            <?php foreach ($files as $file): ?>
                                                                <span class="file-badge"><?php echo htmlspecialchars($file); ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div style="text-align: right;">
                                                    <small class="text-muted">Tokens</small><br>
                                                    <strong><?php echo number_format($change['tokens_used']); ?></strong>
                                                </div>

                                                <button
                                                    class="btn btn-sm btn-secondary"
                                                    onclick="toggleDetails(<?php echo $change['id']; ?>)"
                                                >
                                                    Details
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Expandable Details -->
                                        <div class="change-details" id="details-<?php echo $change['id']; ?>">
                                            <div style="margin-bottom: 1rem;">
                                                <strong>AI Response:</strong>
                                                <div style="margin-top: 0.5rem; white-space: pre-wrap;">
                                                    <?php echo htmlspecialchars($change['ai_response']); ?>
                                                </div>
                                            </div>

                                            <?php if (!$change['success'] && $change['error_message']): ?>
                                                <div style="margin-bottom: 1rem;">
                                                    <strong>Error Message:</strong>
                                                    <div class="error-message">
                                                        <?php echo htmlspecialchars($change['error_message']); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($change['commands_executed']): ?>
                                                <?php $commands = json_decode($change['commands_executed'], true); ?>
                                                <?php if (!empty($commands)): ?>
                                                    <div style="margin-bottom: 1rem;">
                                                        <strong>Commands Executed:</strong>
                                                        <pre style="background: #2d2d2d; color: #fff; padding: 0.5rem; border-radius: 4px; overflow-x: auto; margin-top: 0.5rem;">
<?php foreach ($commands as $cmd): ?>
$ <?php echo htmlspecialchars($cmd); ?>

<?php endforeach; ?>
                                                        </pre>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if ($change['backup_path']): ?>
                                                <div>
                                                    <strong>Backup Location:</strong>
                                                    <code><?php echo htmlspecialchars($change['backup_path']); ?></code>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($change['execution_time_ms'] > 0): ?>
                                                <div style="margin-top: 0.5rem;">
                                                    <small class="text-muted">
                                                        Execution time: <?php echo $change['execution_time_ms']; ?>ms
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary">← Previous</a>
                                    <?php endif; ?>

                                    <span style="padding: 0.5rem 1rem; background: #f0f0f0; border-radius: 4px;">
                                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                                    </span>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary">Next →</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDetails(id) {
            const details = document.getElementById('details-' + id);
            details.classList.toggle('show');
        }
    </script>
</body>
</html>
