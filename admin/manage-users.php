<?php

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$auth = new Auth();
$db = Database::getInstance()->getConnection();
$auth->requireAdmin();

$success = '';
$error = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    // Get user services
    if ($_POST['action'] === 'get_user_services') {
        $userId = $_POST['user_id'] ?? 0;
        $stmt = $db->prepare("
            SELECT s.*, p.name as plan_name, p.price 
            FROM services s 
            LEFT JOIN plans p ON s.plan_id = p.id 
            WHERE s.customer_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'services' => $services]);
        exit;
    }
    
    // Assign service to user
    if ($_POST['action'] === 'assign_service') {
        $userId = $_POST['user_id'] ?? 0;
        $planId = $_POST['plan_id'] ?? 0;
        $domain = $_POST['domain'] ?? '';
        $autoRenew = isset($_POST['auto_renew']) ? 1 : 0;
        
        try {
            $stmt = $db->prepare("
                INSERT INTO services (customer_id, plan_id, domain, status, start_date, renewal_date, expiry_date, auto_renew)
                VALUES (?, ?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), DATE_ADD(NOW(), INTERVAL 1 MONTH), ?)
            ");
            $stmt->execute([$userId, $planId, $domain, $autoRenew]);
            echo json_encode(['success' => true, 'message' => 'Service assigned successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Remove service
    if ($_POST['action'] === 'remove_service') {
        $serviceId = $_POST['service_id'] ?? 0;
        try {
            $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$serviceId]);
            echo json_encode(['success' => true, 'message' => 'Service removed successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // Update service status
    if ($_POST['action'] === 'update_service_status') {
        $serviceId = $_POST['service_id'] ?? 0;
        $status = $_POST['status'] ?? 'active';
        try {
            $stmt = $db->prepare("UPDATE services SET status = ? WHERE id = ?");
            $stmt->execute([$status, $serviceId]);
            echo json_encode(['success' => true, 'message' => 'Service status updated']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// Handle regular form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    // Add new user
    if (isset($_POST['add_user'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        $company = $_POST['company'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';

        if ($email && $password && $fullName) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("
                    INSERT INTO customers (email, password, full_name, company, phone, is_admin, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$email, $hashedPassword, $fullName, $company, $phone, $isAdmin, $status]);
                $success = 'User added successfully!';
            } catch (PDOException $e) {
                $error = 'Failed to add user: ' . $e->getMessage();
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    }

    // Edit user
    if (isset($_POST['edit_user'])) {
        $userId = $_POST['user_id'] ?? 0;
        $email = $_POST['email'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        $company = $_POST['company'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $address = $_POST['address'] ?? null;
        $city = $_POST['city'] ?? null;
        $country = $_POST['country'] ?? null;
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';
        $newPassword = $_POST['new_password'] ?? '';

        if ($userId && $email && $fullName) {
            try {
                if (!empty($newPassword)) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("
                        UPDATE customers
                        SET email = ?, password = ?, full_name = ?, company = ?, phone = ?, address = ?, city = ?, country = ?, is_admin = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$email, $hashedPassword, $fullName, $company, $phone, $address, $city, $country, $isAdmin, $status, $userId]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE customers
                        SET email = ?, full_name = ?, company = ?, phone = ?, address = ?, city = ?, country = ?, is_admin = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$email, $fullName, $company, $phone, $address, $city, $country, $isAdmin, $status, $userId]);
                }
                $success = 'User updated successfully!';
            } catch (PDOException $e) {
                $error = 'Failed to update user: ' . $e->getMessage();
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    }

    // Delete user
    if (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'] ?? 0;
        
        if ($userId && $userId != $auth->getCurrentCustomerId()) {
            try {
                $stmt = $db->prepare("DELETE FROM customers WHERE id = ?");
                $stmt->execute([$userId]);
                $success = 'User deleted successfully!';
            } catch (PDOException $e) {
                $error = 'Failed to delete user: ' . $e->getMessage();
            }
        } else {
            $error = 'Cannot delete your own account or invalid user.';
        }
    }
}

// Get all customers with statistics
$usersStmt = $db->query("
    SELECT c.*,
           (SELECT COUNT(*) FROM services WHERE customer_id = c.id) as service_count,
           (SELECT COUNT(*) FROM tickets WHERE customer_id = c.id) as ticket_count,
           (SELECT COUNT(*) FROM invoices WHERE customer_id = c.id) as invoice_count,
           (SELECT SUM(amount) FROM invoices WHERE customer_id = c.id AND status = 'paid') as total_paid
    FROM customers c
    ORDER BY c.created_at DESC
");
$users = $usersStmt->fetchAll();

// Get all plans for service assignment
$plansStmt = $db->query("SELECT id, name, price, billing_cycle FROM plans WHERE status = 'active' ORDER BY name");
$plans = $plansStmt->fetchAll();

$pageTitle = 'Manage Users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-quick-actions {
            display: flex;
            gap: 5px;
        }
        
        .user-quick-actions .btn {
            padding: 6px 10px;
            font-size: 0.85rem;
        }
        
        .modal-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-tab {
            padding: 12px 20px;
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            font-size: 0.95rem;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s ease;
        }
        
        .modal-tab:hover {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .modal-tab.active {
            color: #00d4ff;
            border-bottom-color: #00d4ff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .service-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .service-info {
            flex: 1;
        }
        
        .service-name {
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
        }
        
        .service-meta {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .service-actions {
            display: flex;
            gap: 8px;
        }
        
        .modal-large {
            max-width: 800px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .modal-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>

        <div class="content">
            <div class="page-header">
                <h1>Manage Users</h1>
                <button class="btn btn-primary" onclick="openAddUserModal()">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>All Users (<?php echo count($users); ?>)</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Services</th>
                                    <th>Tickets</th>
                                    <th>Total Paid</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['company'] ?? 'N/A'); ?></td>
                                        <td><?php echo $user['service_count'] ?? 0; ?></td>
                                        <td><?php echo $user['ticket_count'] ?? 0; ?></td>
                                        <td>$<?php echo number_format($user['total_paid'] ?? 0, 2); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['is_admin'] ? 'warning' : 'info'; ?>">
                                                <?php echo $user['is_admin'] ? 'Admin' : 'Customer'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'error'; ?>">
                                                <?php echo htmlspecialchars($user['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="user-quick-actions">
                                                <button onclick='openEditUserModal(<?php echo json_encode($user); ?>)' 
                                                        class="btn btn-primary" title="Edit & Manage">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['id'] != $auth->getCurrentCustomerId()): ?>
                                                    <button onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['full_name'])); ?>')" 
                                                            class="btn btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add New User</h2>
                <button class="close-modal" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_email">Email Address *</label>
                        <input type="email" id="add_email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="add_password">Password *</label>
                        <input type="password" id="add_password" name="password" required minlength="8">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="add_full_name">Full Name *</label>
                        <input type="text" id="add_full_name" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="add_company">Company</label>
                        <input type="text" id="add_company" name="company">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="add_phone">Phone</label>
                        <input type="tel" id="add_phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="add_status">Status</label>
                        <select id="add_status" name="status">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="is_admin" id="add_is_admin">
                        <span>Administrator Account</span>
                    </label>
                </div>

                <button type="submit" name="add_user" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                    <i class="fas fa-user-plus"></i> Add User
                </button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal with Tabs -->
    <div id="editUserModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 class="modal-title">Manage User: <span id="editUserName"></span></h2>
                <button class="close-modal" onclick="closeModal('editUserModal')">&times;</button>
            </div>

            <div class="modal-tabs">
                <button class="modal-tab active" onclick="switchTab('userInfo')">
                    <i class="fas fa-user"></i> User Info
                </button>
                <button class="modal-tab" onclick="switchTab('userServices')">
                    <i class="fas fa-server"></i> Services (<span id="serviceCount">0</span>)
                </button>
                <button class="modal-tab" onclick="switchTab('assignService')">
                    <i class="fas fa-plus-circle"></i> Assign Service
                </button>
            </div>

            <!-- Tab: User Info -->
            <div id="userInfo" class="tab-content active">
                <form method="POST">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_email">Email Address *</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_full_name">Full Name *</label>
                            <input type="text" id="edit_full_name" name="full_name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_company">Company</label>
                            <input type="text" id="edit_company" name="company">
                        </div>

                        <div class="form-group">
                            <label for="edit_phone">Phone</label>
                            <input type="tel" id="edit_phone" name="phone">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_address">Address</label>
                        <input type="text" id="edit_address" name="address">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_city">City</label>
                            <input type="text" id="edit_city" name="city">
                        </div>

                        <div class="form-group">
                            <label for="edit_country">Country</label>
                            <input type="text" id="edit_country" name="country">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_new_password">New Password (leave blank to keep current)</label>
                            <input type="password" id="edit_new_password" name="new_password" minlength="8">
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="is_admin" id="edit_is_admin">
                            <span>Administrator Account</span>
                        </label>
                    </div>

                    <button type="submit" name="edit_user" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Tab: User Services -->
            <div id="userServices" class="tab-content">
                <div id="userServicesList">
                    <p style="text-align: center; color: rgba(255,255,255,0.6); padding: 40px 0;">
                        <i class="fas fa-spinner fa-spin"></i> Loading services...
                    </p>
                </div>
            </div>

            <!-- Tab: Assign Service -->
            <div id="assignService" class="tab-content">
                <form id="assignServiceForm" onsubmit="assignServiceToUser(event)">
                    <div class="form-group">
                        <label for="assign_plan_id">Select Plan/Service *</label>
                        <select id="assign_plan_id" name="plan_id" required>
                            <option value="">-- Select a plan --</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?php echo $plan['id']; ?>">
                                    <?php echo htmlspecialchars($plan['name']); ?> 
                                    - $<?php echo number_format($plan['price'], 2); ?>/<?php echo $plan['billing_cycle']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="assign_domain">Domain (optional)</label>
                        <input type="text" id="assign_domain" name="domain" placeholder="example.com">
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="auto_renew" id="assign_auto_renew" checked>
                            <span>Auto-renew enabled</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus-circle"></i> Assign Service
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <h2 style="color: #ff4444; margin-bottom: 15px;">
                <i class="fas fa-exclamation-triangle"></i> Confirm Delete
            </h2>
            <p style="margin-bottom: 20px; color: rgba(255,255,255,0.8);">
                Are you sure you want to delete user <strong id="deleteUserName"></strong>?
            </p>
            <form method="POST">
                <input type="hidden" name="user_id" id="delete_user_id">
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeModal('deleteModal')" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-danger" style="flex: 1;">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        let currentUserId = null;

        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }

        function openEditUserModal(user) {
            currentUserId = user.id;
            document.getElementById('editUserName').textContent = user.full_name;
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_company').value = user.company || '';
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_address').value = user.address || '';
            document.getElementById('edit_city').value = user.city || '';
            document.getElementById('edit_country').value = user.country || '';
            document.getElementById('edit_status').value = user.status;
            document.getElementById('edit_is_admin').checked = user.is_admin == 1;
            
            // Switch to user info tab
            switchTab('userInfo');
            
            // Load user services
            loadUserServices(user.id);
            
            document.getElementById('editUserModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function confirmDelete(userId, userName) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Deactivate all tab buttons
            document.querySelectorAll('.modal-tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Activate corresponding button
            event.target.classList.add('active');
            
            // Load services if services tab is opened
            if (tabName === 'userServices' && currentUserId) {
                loadUserServices(currentUserId);
            }
        }

        function loadUserServices(userId) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'get_user_services');
            formData.append('user_id', userId);

            fetch('manage-users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const servicesList = document.getElementById('userServicesList');
                document.getElementById('serviceCount').textContent = data.services.length;
                
                if (data.services.length === 0) {
                    servicesList.innerHTML = '<p style="text-align: center; color: rgba(255,255,255,0.6); padding: 40px 0;"><i class="fas fa-inbox"></i> No services assigned yet</p>';
                    return;
                }
                
                let html = '';
                data.services.forEach(service => {
                    const statusColor = service.status === 'active' ? 'success' : service.status === 'suspended' ? 'warning' : 'error';
                    html += `
                        <div class="service-item">
                            <div class="service-info">
                                <div class="service-name">${service.plan_name || 'Unknown Plan'}</div>
                                <div class="service-meta">
                                    <span class="badge badge-${statusColor}">${service.status}</span>
                                    ${service.domain ? ' • ' + service.domain : ''}
                                    ${service.expiry_date ? ' • Expires: ' + new Date(service.expiry_date).toLocaleDateString() : ''}
                                </div>
                            </div>
                            <div class="service-actions">
                                ${service.status === 'active' ? 
                                    `<button onclick="updateServiceStatus(${service.id}, 'suspended')" class="btn btn-sm btn-warning" title="Suspend">
                                        <i class="fas fa-pause"></i>
                                    </button>` :
                                    `<button onclick="updateServiceStatus(${service.id}, 'active')" class="btn btn-sm btn-success" title="Activate">
                                        <i class="fas fa-play"></i>
                                    </button>`
                                }
                                <button onclick="removeService(${service.id})" class="btn btn-sm btn-danger" title="Remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                servicesList.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading services:', error);
                document.getElementById('userServicesList').innerHTML = '<p style="color: #ff4444;">Error loading services</p>';
            });
        }

        function assignServiceToUser(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('ajax', '1');
            formData.append('action', 'assign_service');
            formData.append('user_id', currentUserId);

            fetch('manage-users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Service assigned successfully!');
                    event.target.reset();
                    loadUserServices(currentUserId);
                    switchTab('userServices');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while assigning service');
            });
        }

        function removeService(serviceId) {
            if (!confirm('Are you sure you want to remove this service?')) return;
            
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'remove_service');
            formData.append('service_id', serviceId);

            fetch('manage-users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Service removed successfully!');
                    loadUserServices(currentUserId);
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        function updateServiceStatus(serviceId, status) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'update_service_status');
            formData.append('service_id', serviceId);
            formData.append('status', status);

            fetch('manage-users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Service status updated!');
                    loadUserServices(currentUserId);
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
