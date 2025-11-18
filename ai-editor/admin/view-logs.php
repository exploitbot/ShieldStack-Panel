<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

// Pagination and filters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

$filterCustomer = $_GET['customer'] ?? '';
$filterSuccess = isset($_GET['success']) ? $_GET['success'] : '';

// Build query
$where = [];
$params = [];

if ($filterCustomer) {
    $where[] = "c.customer_id = ?";
    $params[] = $filterCustomer;
}

if ($filterSuccess !== '') {
    $where[] = "c.success = ?";
    $params[] = intval($filterSuccess);
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM ai_change_logs c $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get logs
$stmt = $db->prepare("
    SELECT
        c.*,
        cu.full_name,
        cu.email,
        s.website_name,
        s.website_url
    FROM ai_change_logs c
    JOIN customers cu ON c.customer_id = cu.id
    LEFT JOIN customer_ssh_credentials s ON c.ssh_credential_id = s.id
    $whereClause
    ORDER BY c.executed_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$logs = $stmt->fetchAll();

// Get customers for filter
$customers = $db->query("
    SELECT DISTINCT cu.id, cu.full_name
    FROM customers cu
    JOIN ai_change_logs c ON cu.id = c.customer_id
    ORDER BY cu.full_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View AI Logs - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .filters {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../admin/includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../../admin/includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">📝 AI Change Logs</h1>
                    <p class="page-subtitle">View all AI-executed changes across all customers</p>
                </div>

                <!-- Filters -->
                <form method="GET" class="filters">
                    <div class="filter-group">
                        <label>Customer</label>
                        <select name="customer" class="form-control">
                            <option value="">All Customers</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>" <?php echo $filterCustomer == $customer['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status</label>
                        <select name="success" class="form-control">
                            <option value="">All</option>
                            <option value="1" <?php echo $filterSuccess === '1' ? 'selected' : ''; ?>>Success Only</option>
                            <option value="0" <?php echo $filterSuccess === '0' ? 'selected' : ''; ?>>Failed Only</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="view-logs.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>

                <!-- Logs -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Logs (<?php echo number_format($totalRecords); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($logs)): ?>
                            <p class="text-muted">No logs found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Request</th>
                                            <th>Website</th>
                                            <th>Tokens</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($log['full_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($log['email']); ?></small>
                                                </td>
                                                <td>
                                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?php echo htmlspecialchars($log['user_request']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($log['website_name']): ?>
                                                        <small><?php echo htmlspecialchars($log['website_name']); ?></small>
                                                    <?php elseif ($log['website_url']): ?>
                                                        <small><?php echo htmlspecialchars($log['website_url']); ?></small>
                                                    <?php else: ?>
                                                        <small class="text-muted">N/A</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo number_format($log['tokens_used']); ?></td>
                                                <td>
                                                    <?php if ($log['success']): ?>
                                                        <span class="badge badge-success">✓</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">✗</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M d, H:i', strtotime($log['executed_at'])); ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div style="margin-top: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>&customer=<?php echo $filterCustomer; ?>&success=<?php echo $filterSuccess; ?>" class="btn btn-secondary">← Previous</a>
                                    <?php endif; ?>

                                    <span style="padding: 0.5rem 1rem; background: #f0f0f0; border-radius: 4px;">
                                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                                    </span>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&customer=<?php echo $filterCustomer; ?>&success=<?php echo $filterSuccess; ?>" class="btn btn-secondary">Next →</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
