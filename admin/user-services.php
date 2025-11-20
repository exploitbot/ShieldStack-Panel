<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$userId = $_GET['user_id'] ?? 0;

if (!$userId) {
    header('Location: manage-users.php');
    exit;
}

// Get user info
$userStmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

if (!$user) {
    header('Location: manage-users.php');
    exit;
}

$success = '';
$error = '';

// Handle service actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add service
    if (isset($_POST['add_service'])) {
        $planId = $_POST['plan_id'] ?? 0;
        $domain = $_POST['domain'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $startDate = $_POST['start_date'] ?? date('Y-m-d H:i:s');
        $renewalDate = $_POST['renewal_date'] ?? date('Y-m-d H:i:s', strtotime('+1 month'));
        $expiryDate = $_POST['expiry_date'] ?? date('Y-m-d H:i:s', strtotime('+1 month'));
        $autoRenew = isset($_POST['auto_renew']) ? 1 : 0;

        try {
            $stmt = $db->prepare("
                INSERT INTO services (customer_id, plan_id, domain, status, start_date, renewal_date, expiry_date, auto_renew)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $planId, $domain, $status, $startDate, $renewalDate, $expiryDate, $autoRenew]);
            $success = 'Service added successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to add service: ' . $e->getMessage();
        }
    }

    // Update service
    if (isset($_POST['update_service'])) {
        $serviceId = $_POST['service_id'] ?? 0;
        $domain = $_POST['domain'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $renewalDate = $_POST['renewal_date'] ?? null;
        $expiryDate = $_POST['expiry_date'] ?? null;
        $autoRenew = isset($_POST['auto_renew']) ? 1 : 0;
        $notes = $_POST['notes'] ?? '';

        try {
            $stmt = $db->prepare("
                UPDATE services
                SET domain = ?, status = ?, renewal_date = ?, expiry_date = ?, auto_renew = ?, notes = ?
                WHERE id = ? AND customer_id = ?
            ");
            $stmt->execute([$domain, $status, $renewalDate, $expiryDate, $autoRenew, $notes, $serviceId, $userId]);
            $success = 'Service updated successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to update service: ' . $e->getMessage();
        }
    }

    // Suspend service
    if (isset($_POST['suspend_service'])) {
        $serviceId = $_POST['service_id'] ?? 0;
        $reason = $_POST['suspension_reason'] ?? 'No reason provided';

        try {
            $stmt = $db->prepare("
                UPDATE services
                SET suspended = 1, suspension_reason = ?, status = 'suspended'
                WHERE id = ? AND customer_id = ?
            ");
            $stmt->execute([$reason, $serviceId, $userId]);
            $success = 'Service suspended successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to suspend service: ' . $e->getMessage();
        }
    }

    // Unsuspend service
    if (isset($_POST['unsuspend_service'])) {
        $serviceId = $_POST['service_id'] ?? 0;

        try {
            $stmt = $db->prepare("
                UPDATE services
                SET suspended = 0, suspension_reason = NULL, status = 'active'
                WHERE id = ? AND customer_id = ?
            ");
            $stmt->execute([$serviceId, $userId]);
            $success = 'Service reactivated successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to reactivate service: ' . $e->getMessage();
        }
    }

    // Delete service
    if (isset($_POST['delete_service'])) {
        $serviceId = $_POST['service_id'] ?? 0;

        try {
            $stmt = $db->prepare("DELETE FROM services WHERE id = ? AND customer_id = ?");
            $stmt->execute([$serviceId, $userId]);
            $success = 'Service deleted successfully!';
        } catch (PDOException $e) {
            $error = 'Failed to delete service: ' . $e->getMessage();
        }
    }
}

// Get user services with all plan details
$servicesStmt = $db->prepare("
    SELECT s.*, p.name as plan_name, p.type, p.price, p.billing_cycle, p.category,
           p.disk_space, p.bandwidth, p.`databases`, p.email_accounts, p.subdomains,
           p.ftp_accounts, p.ssl_certificates, p.daily_backups, p.support_level, p.features
    FROM services s
    JOIN plans p ON s.plan_id = p.id
    WHERE s.customer_id = ?
    ORDER BY s.created_at DESC
");
$servicesStmt->execute([$userId]);
$services = $servicesStmt->fetchAll();

// Get all plans
$plansStmt = $db->query("SELECT * FROM plans WHERE status = 'active' ORDER BY category, name");
$plans = $plansStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - <?php echo htmlspecialchars($user['full_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .service-details {
            background: var(--background);
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            border-left: 3px solid var(--primary-color);
        }
        .service-details h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            font-size: 13px;
        }
        .detail-item {
            color: var(--text-secondary);
        }
        .detail-item strong {
            color: var(--text-primary);
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
                    <h1 class="page-title">Services for <?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <p class="page-subtitle"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <a href="manage-users.php" class="btn btn-secondary">← Back to Users</a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Services (<?php echo count($services); ?>)</h2>
                        <button onclick="openAddServiceModal()" class="btn btn-primary">Add Service</button>
                    </div>
                    <div class="card-body">
                        <?php if (count($services) > 0): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Plan</th>
                                            <th>Domain</th>
                                            <th>Status</th>
                                            <th>Price</th>
                                            <th>Expiry Date</th>
                                            <th>Auto-Renew</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services as $service): ?>
                                            <tr>
                                                <td>#<?php echo $service['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($service['plan_name']); ?></strong><br>
                                                    <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($service['category']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($service['domain'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if ($service['suspended']): ?>
                                                        <span class="badge badge-error">Suspended</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-<?php
                                                            echo $service['status'] === 'active' ? 'success' :
                                                                ($service['status'] === 'pending' ? 'warning' : 'error');
                                                        ?>">
                                                            <?php echo htmlspecialchars($service['status']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>$<?php echo number_format($service['price'], 2); ?>/<?php echo $service['billing_cycle'];?></td>
                                                <td><?php echo $service['expiry_date'] ? date('M d, Y', strtotime($service['expiry_date'])) : 'N/A'; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $service['auto_renew'] ? 'success' : 'error'; ?>">
                                                        <?php echo $service['auto_renew'] ? 'Yes' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button onclick='viewService(<?php echo htmlspecialchars(json_encode($service)); ?>)' class="btn btn-secondary">View</button>
                                                        <button onclick='openEditServiceModal(<?php echo htmlspecialchars(json_encode($service)); ?>)' class="btn btn-secondary">Edit</button>
                                                        <?php if ($service['suspended']): ?>
                                                            <button onclick="unsuspendService(<?php echo $service['id']; ?>)" class="btn btn-success">Unsuspend</button>
                                                        <?php else: ?>
                                                            <button onclick="openSuspendModal(<?php echo $service['id']; ?>)" class="btn btn-danger">Suspend</button>
                                                        <?php endif; ?>
                                                        <button onclick="confirmDeleteService(<?php echo $service['id']; ?>)" class="btn btn-danger">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-secondary); text-align: center; padding: 40px;">
                                No services yet. Click "Add Service" to create one.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">Add New Service</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="add_plan_id">Plan *</label>
                    <select id="add_plan_id" name="plan_id" required>
                        <option value="">Select a plan</option>
                        <?php 
                        $currentCategory = '';
                        foreach ($plans as $plan): 
                            if ($currentCategory !== $plan['category']) {
                                if ($currentCategory !== '') echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars(ucfirst($plan['category'])) . '">';
                                $currentCategory = $plan['category'];
                            }
                        ?>
                            <option value="<?php echo $plan['id']; ?>">
                                <?php echo htmlspecialchars($plan['name']); ?> - $<?php echo number_format($plan['price'], 2); ?>/<?php echo $plan['billing_cycle'];?>
                            </option>
                        <?php endforeach; ?>
                        <?php if ($currentCategory !== '') echo '</optgroup>'; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="add_domain">Domain</label>
                    <input type="text" id="add_domain" name="domain" placeholder="example.com">
                </div>

                <div class="form-group">
                    <label for="add_status">Status</label>
                    <select id="add_status" name="status">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="add_start_date">Start Date</label>
                    <input type="datetime-local" id="add_start_date" name="start_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>

                <div class="form-group">
                    <label for="add_renewal_date">Renewal Date</label>
                    <input type="datetime-local" id="add_renewal_date" name="renewal_date" value="<?php echo date('Y-m-d\TH:i', strtotime('+1 month')); ?>">
                </div>

                <div class="form-group">
                    <label for="add_expiry_date">Expiry Date</label>
                    <input type="datetime-local" id="add_expiry_date" name="expiry_date" value="<?php echo date('Y-m-d\TH:i', strtotime('+1 month')); ?>">
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="auto_renew" value="1" checked>
                        <span>Auto-renew enabled</span>
                    </label>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="add_service" class="btn btn-primary" style="flex: 1;">Add Service</button>
                    <button type="button" onclick="closeAddServiceModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="editServiceModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">Edit Service</h2>
            <form method="POST">
                <input type="hidden" id="edit_service_id" name="service_id">

                <div class="form-group">
                    <label>Plan</label>
                    <input type="text" id="edit_plan_name" readonly style="background: var(--background);">
                </div>

                <div class="form-group">
                    <label for="edit_domain">Domain</label>
                    <input type="text" id="edit_domain" name="domain">
                </div>

                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_renewal_date">Renewal Date</label>
                    <input type="datetime-local" id="edit_renewal_date" name="renewal_date">
                </div>

                <div class="form-group">
                    <label for="edit_expiry_date">Expiry Date</label>
                    <input type="datetime-local" id="edit_expiry_date" name="expiry_date">
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit_auto_renew" name="auto_renew" value="1">
                        <span>Auto-renew enabled</span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="edit_notes">Notes</label>
                    <textarea id="edit_notes" name="notes" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="update_service" class="btn btn-primary" style="flex: 1;">Update Service</button>
                    <button type="button" onclick="closeEditServiceModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Service Modal -->
    <div id="viewServiceModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">Service Details</h2>
            <div id="serviceDetailsContent"></div>
            <div style="margin-top: 20px;">
                <button type="button" onclick="closeViewServiceModal()" class="btn btn-secondary" style="width: 100%;">Close</button>
            </div>
        </div>
    </div>

    <!-- Suspend Service Modal -->
    <div id="suspendServiceModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px; color: var(--error);">Suspend Service</h2>
            <form method="POST">
                <input type="hidden" id="suspend_service_id" name="service_id">
                
                <div class="form-group">
                    <label for="suspension_reason">Suspension Reason *</label>
                    <textarea id="suspension_reason" name="suspension_reason" rows="3" required placeholder="Please provide a reason for suspending this service..."></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="suspend_service" class="btn btn-danger" style="flex: 1;">Suspend Service</button>
                    <button type="button" onclick="closeSuspendModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Service Form -->
    <form id="deleteServiceForm" method="POST" style="display: none;">
        <input type="hidden" name="service_id" id="delete_service_id">
        <input type="hidden" name="delete_service" value="1">
    </form>

    <!-- Unsuspend Service Form -->
    <form id="unsuspendServiceForm" method="POST" style="display: none;">
        <input type="hidden" name="service_id" id="unsuspend_service_id">
        <input type="hidden" name="unsuspend_service" value="1">
    </form>

    <script>
        function openAddServiceModal() {
            document.getElementById('addServiceModal').classList.add('active');
        }

        function closeAddServiceModal() {
            document.getElementById('addServiceModal').classList.remove('active');
        }

        function openEditServiceModal(service) {
            document.getElementById('edit_service_id').value = service.id;
            document.getElementById('edit_plan_name').value = service.plan_name + ' - $' + service.price + '/' + service.billing_cycle;
            document.getElementById('edit_domain').value = service.domain || '';
            document.getElementById('edit_status').value = service.status;
            
            if (service.renewal_date) {
                const renewalDate = new Date(service.renewal_date);
                document.getElementById('edit_renewal_date').value = renewalDate.toISOString().slice(0, 16);
            }
            
            if (service.expiry_date) {
                const expiryDate = new Date(service.expiry_date);
                document.getElementById('edit_expiry_date').value = expiryDate.toISOString().slice(0, 16);
            }
            
            document.getElementById('edit_auto_renew').checked = service.auto_renew == 1;
            document.getElementById('edit_notes').value = service.notes || '';
            document.getElementById('editServiceModal').classList.add('active');
        }

        function closeEditServiceModal() {
            document.getElementById('editServiceModal').classList.remove('active');
        }

        function viewService(service) {
            let features = [];
            try {
                features = service.features ? JSON.parse(service.features) : [];
            } catch (e) {
                features = [];
            }

            const html = `
                <div class="service-details">
                    <h4>Plan Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item"><strong>Plan:</strong> ${service.plan_name}</div>
                        <div class="detail-item"><strong>Category:</strong> ${service.category}</div>
                        <div class="detail-item"><strong>Price:</strong> $${parseFloat(service.price).toFixed(2)}/${service.billing_cycle}</div>
                        <div class="detail-item"><strong>Disk Space:</strong> ${service.disk_space || 'N/A'}</div>
                        <div class="detail-item"><strong>Bandwidth:</strong> ${service.bandwidth || 'N/A'}</div>
                        <div class="detail-item"><strong>Databases:</strong> ${service.databases || 0}</div>
                        <div class="detail-item"><strong>Email Accounts:</strong> ${service.email_accounts || 0}</div>
                        <div class="detail-item"><strong>Subdomains:</strong> ${service.subdomains || 0}</div>
                        <div class="detail-item"><strong>FTP Accounts:</strong> ${service.ftp_accounts || 0}</div>
                        <div class="detail-item"><strong>SSL:</strong> ${service.ssl_certificates ? 'Yes' : 'No'}</div>
                        <div class="detail-item"><strong>Backups:</strong> ${service.daily_backups ? 'Daily' : 'No'}</div>
                        <div class="detail-item"><strong>Support:</strong> ${service.support_level || 'Standard'}</div>
                    </div>
                </div>
                <div class="service-details">
                    <h4>Service Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item"><strong>Service ID:</strong> #${service.id}</div>
                        <div class="detail-item"><strong>Domain:</strong> ${service.domain || 'N/A'}</div>
                        <div class="detail-item"><strong>Status:</strong> ${service.status}</div>
                        <div class="detail-item"><strong>Start Date:</strong> ${service.start_date ? new Date(service.start_date).toLocaleDateString() : 'N/A'}</div>
                        <div class="detail-item"><strong>Renewal Date:</strong> ${service.renewal_date ? new Date(service.renewal_date).toLocaleDateString() : 'N/A'}</div>
                        <div class="detail-item"><strong>Expiry Date:</strong> ${service.expiry_date ? new Date(service.expiry_date).toLocaleDateString() : 'N/A'}</div>
                        <div class="detail-item"><strong>Auto-Renew:</strong> ${service.auto_renew ? 'Enabled' : 'Disabled'}</div>
                        <div class="detail-item"><strong>Suspended:</strong> ${service.suspended ? 'Yes' : 'No'}</div>
                    </div>
                    ${service.suspended && service.suspension_reason ? `<div style="margin-top:10px;"><strong>Suspension Reason:</strong><br>${service.suspension_reason}</div>` : ''}
                    ${service.notes ? `<div style="margin-top:10px;"><strong>Notes:</strong><br>${service.notes}</div>` : ''}
                </div>
                ${features.length > 0 ? `
                <div class="service-details">
                    <h4>Plan Features</h4>
                    <ul class="plan-features" style="list-style: none; padding: 0;">
                        ${features.map(f => `<li style="padding: 5px 0; color: var(--text-primary);">✓ ${f}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
            `;
            
            document.getElementById('serviceDetailsContent').innerHTML = html;
            document.getElementById('viewServiceModal').classList.add('active');
        }

        function closeViewServiceModal() {
            document.getElementById('viewServiceModal').classList.remove('active');
        }

        function openSuspendModal(serviceId) {
            document.getElementById('suspend_service_id').value = serviceId;
            document.getElementById('suspendServiceModal').classList.add('active');
        }

        function closeSuspendModal() {
            document.getElementById('suspendServiceModal').classList.remove('active');
        }

        function unsuspendService(serviceId) {
            if (confirm('Are you sure you want to reactivate this service?')) {
                document.getElementById('unsuspend_service_id').value = serviceId;
                document.getElementById('unsuspendServiceForm').submit();
            }
        }

        function confirmDeleteService(serviceId) {
            if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
                document.getElementById('delete_service_id').value = serviceId;
                document.getElementById('deleteServiceForm').submit();
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
