<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../includes/encryption.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$encryption = new CredentialEncryption();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            try {
                $customerId = $_POST['customer_id'];
                $websiteName = $_POST['website_name'];
                $sshHost = $_POST['ssh_host'];
                $sshPort = $_POST['ssh_port'] ?: 22;
                $sshUsername = $_POST['ssh_username'];
                $sshPassword = $_POST['ssh_password'];
                $webRootPath = $_POST['web_root_path'] ?: '/var/www/html';
                $websiteUrl = $_POST['website_url'];
                $websiteType = $_POST['website_type'];

                // Encrypt password
                $encryptedPassword = !empty($sshPassword) ? $encryption->encrypt($sshPassword) : '';

                if ($_POST['action'] === 'add') {
                    $stmt = $db->prepare("
                        INSERT INTO customer_ssh_credentials
                        (customer_id, website_name, ssh_host, ssh_port, ssh_username, ssh_password_encrypted,
                         web_root_path, website_url, website_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $customerId, $websiteName, $sshHost, $sshPort, $sshUsername,
                        $encryptedPassword, $webRootPath, $websiteUrl, $websiteType
                    ]);
                    $success = 'SSH credentials added successfully!';
                } else {
                    $credId = $_POST['cred_id'];
                    if (!empty($sshPassword)) {
                        $stmt = $db->prepare("
                            UPDATE customer_ssh_credentials
                            SET website_name = ?, ssh_host = ?, ssh_port = ?, ssh_username = ?,
                                ssh_password_encrypted = ?, web_root_path = ?, website_url = ?, website_type = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $websiteName, $sshHost, $sshPort, $sshUsername, $encryptedPassword,
                            $webRootPath, $websiteUrl, $websiteType, $credId
                        ]);
                    } else {
                        $stmt = $db->prepare("
                            UPDATE customer_ssh_credentials
                            SET website_name = ?, ssh_host = ?, ssh_port = ?, ssh_username = ?,
                                web_root_path = ?, website_url = ?, website_type = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $websiteName, $sshHost, $sshPort, $sshUsername,
                            $webRootPath, $websiteUrl, $websiteType, $credId
                        ]);
                    }
                    $success = 'SSH credentials updated successfully!';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete') {
            try {
                $credId = $_POST['cred_id'];
                $stmt = $db->prepare("DELETE FROM customer_ssh_credentials WHERE id = ?");
                $stmt->execute([$credId]);
                $success = 'SSH credentials deleted successfully!';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'toggle_status') {
            try {
                $credId = $_POST['cred_id'];
                $stmt = $db->prepare("UPDATE customer_ssh_credentials SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$credId]);
                $success = 'Status updated successfully!';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}

// Get all SSH credentials
$credentials = $db->query("
    SELECT
        c.*,
        cu.full_name,
        cu.email
    FROM customer_ssh_credentials c
    JOIN customers cu ON c.customer_id = cu.id
    ORDER BY c.created_at DESC
")->fetchAll();

// Get customers for dropdown
$customers = $db->query("SELECT id, full_name, email FROM customers ORDER BY full_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage SSH Credentials - AI Editor Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../admin/includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include '../../admin/includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">🔐 Manage SSH Credentials</h1>
                    <p class="page-subtitle">Configure customer SSH access for AI website editing</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Add New SSH Credential -->
                <div class="card">
                    <div class="card-header">
                        <h3>Add New SSH Credentials</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="form">
                            <input type="hidden" name="action" value="add">

                            <div class="form-group">
                                <label>Customer *</label>
                                <select name="customer_id" required class="form-control">
                                    <option value="">Select customer...</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>">
                                            <?php echo htmlspecialchars($customer['full_name'] . ' (' . $customer['email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Website Name</label>
                                    <input type="text" name="website_name" class="form-control" placeholder="My Website">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Website URL</label>
                                    <input type="url" name="website_url" class="form-control" placeholder="https://example.com">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label>SSH Host *</label>
                                    <input type="text" name="ssh_host" required class="form-control" placeholder="server.example.com">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>SSH Port</label>
                                    <input type="number" name="ssh_port" class="form-control" placeholder="22" value="22">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>SSH Username *</label>
                                    <input type="text" name="ssh_username" required class="form-control" placeholder="root">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>SSH Password *</label>
                                    <input type="password" name="ssh_password" required class="form-control">
                                    <small class="form-text text-muted">Will be encrypted before storing</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label>Web Root Path *</label>
                                    <input type="text" name="web_root_path" class="form-control" placeholder="/var/www/html" value="/var/www/html">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Website Type</label>
                                    <select name="website_type" class="form-control">
                                        <option value="custom">Custom</option>
                                        <option value="wordpress">WordPress</option>
                                        <option value="html">Static HTML</option>
                                        <option value="php">PHP</option>
                                        <option value="laravel">Laravel</option>
                                        <option value="react">React</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Add SSH Credentials</button>
                        </form>
                    </div>
                </div>

                <!-- Existing SSH Credentials -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Existing SSH Credentials</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($credentials)): ?>
                            <p class="text-muted">No SSH credentials configured yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Website</th>
                                            <th>SSH Details</th>
                                            <th>Web Root</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($credentials as $cred): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($cred['full_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($cred['email']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($cred['website_name']): ?>
                                                        <strong><?php echo htmlspecialchars($cred['website_name']); ?></strong><br>
                                                    <?php endif; ?>
                                                    <?php if ($cred['website_url']): ?>
                                                        <a href="<?php echo htmlspecialchars($cred['website_url']); ?>" target="_blank" class="text-muted">
                                                            <?php echo htmlspecialchars($cred['website_url']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <code><?php echo htmlspecialchars($cred['ssh_username']); ?>@<?php echo htmlspecialchars($cred['ssh_host']); ?>:<?php echo $cred['ssh_port']; ?></code>
                                                </td>
                                                <td><code><?php echo htmlspecialchars($cred['web_root_path']); ?></code></td>
                                                <td><span class="badge"><?php echo ucfirst($cred['website_type']); ?></span></td>
                                                <td>
                                                    <?php if ($cred['is_active']): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="cred_id" value="<?php echo $cred['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-secondary">
                                                            <?php echo $cred['is_active'] ? 'Disable' : 'Enable'; ?>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete these SSH credentials?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="cred_id" value="<?php echo $cred['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
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
