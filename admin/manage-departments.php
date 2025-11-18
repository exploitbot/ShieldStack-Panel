<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_department'])) {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $email = $_POST['email'] ?? '';
        $auto_response = $_POST['auto_response'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        if ($name) {
            $stmt = $db->prepare("INSERT INTO ticket_departments (name, description, email, auto_response, status) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $description, $email, $auto_response, $status])) {
                $success = 'Department added successfully!';
            } else {
                $error = 'Failed to add department.';
            }
        }
    } elseif (isset($_POST['edit_department'])) {
        $id = $_POST['department_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $email = $_POST['email'] ?? '';
        $auto_response = $_POST['auto_response'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        $stmt = $db->prepare("UPDATE ticket_departments SET name=?, description=?, email=?, auto_response=?, status=? WHERE id=?");
        if ($stmt->execute([$name, $description, $email, $auto_response, $status, $id])) {
            $success = 'Department updated successfully!';
        } else {
            $error = 'Failed to update department.';
        }
    } elseif (isset($_POST['delete_department'])) {
        $id = $_POST['department_id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM ticket_departments WHERE id=? AND is_default=0");
        if ($stmt->execute([$id])) {
            $success = 'Department deleted successfully!';
        } else {
            $error = 'Cannot delete default department.';
        }
    } elseif (isset($_POST['set_default'])) {
        $id = $_POST['department_id'] ?? 0;
        $db->exec("UPDATE ticket_departments SET is_default=0");
        $stmt = $db->prepare("UPDATE ticket_departments SET is_default=1 WHERE id=?");
        if ($stmt->execute([$id])) {
            $success = 'Default department updated!';
        }
    }
}

// Get all departments
$departmentsStmt = $db->query("SELECT * FROM ticket_departments ORDER BY is_default DESC, name ASC");
$departments = $departmentsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - ShieldStack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Ticket Departments</h1>
                    <p class="page-subtitle">Manage support ticket departments</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Department</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="name">Department Name *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="email">Department Email</label>
                                <input type="email" id="email" name="email">
                            </div>
                            <div class="form-group">
                                <label for="auto_response">Auto-Response Message</label>
                                <textarea id="auto_response" name="auto_response" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Existing Departments</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Default</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $dept): ?>
                                        <tr>
                                            <td><?php echo $dept['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($dept['email'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $dept['status'] === 'active' ? 'success' : 'error'; ?>">
                                                    <?php echo ucfirst($dept['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($dept['is_default']): ?>
                                                    <span class="badge badge-info">Default</span>
                                                <?php else: ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="department_id" value="<?php echo $dept['id']; ?>">
                                                        <button type="submit" name="set_default" class="btn btn-secondary" style="padding:4px 8px;font-size:12px;">Set Default</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button onclick='editDepartment(<?php echo json_encode($dept); ?>)' class="btn btn-secondary" style="padding:6px 12px;margin-right:5px;">Edit</button>
                                                <?php if (!$dept['is_default']): ?>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                                        <input type="hidden" name="department_id" value="<?php echo $dept['id']; ?>">
                                                        <button type="submit" name="delete_department" class="btn btn-danger" style="padding:6px 12px;">Delete</button>
                                                    </form>
                                                <?php endif; ?>
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
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Department</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="department_id" id="edit_id">
                <div class="form-group">
                    <label for="edit_name">Department Name *</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_email">Department Email</label>
                    <input type="email" id="edit_email" name="email">
                </div>
                <div class="form-group">
                    <label for="edit_auto_response">Auto-Response Message</label>
                    <textarea id="edit_auto_response" name="auto_response" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" name="edit_department" class="btn btn-primary">Update Department</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        function editDepartment(dept) {
            document.getElementById('edit_id').value = dept.id;
            document.getElementById('edit_name').value = dept.name;
            document.getElementById('edit_description').value = dept.description || '';
            document.getElementById('edit_email').value = dept.email || '';
            document.getElementById('edit_auto_response').value = dept.auto_response || '';
            document.getElementById('edit_status').value = dept.status;
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
