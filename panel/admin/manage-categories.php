<?php

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();
$db = Database::getInstance()->getConnection();

// Create categories table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$db->exec($createTableSQL);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add') {
        try {
            $stmt = $db->prepare("INSERT INTO product_categories (name, description, icon, display_order, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['icon'],
                $_POST['display_order'],
                $_POST['status']
            ]);
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'edit') {
        try {
            $stmt = $db->prepare("UPDATE product_categories SET name = ?, description = ?, icon = ?, display_order = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['icon'],
                $_POST['display_order'],
                $_POST['status'],
                $_POST['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'delete') {
        try {
            // Check if any products use this category
            $stmt = $db->prepare("SELECT COUNT(*) FROM plans WHERE category = (SELECT name FROM product_categories WHERE id = ?)");
            $stmt->execute([$_POST['id']]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete category: ' . $count . ' products are using it']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM product_categories WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'get') {
        try {
            $stmt = $db->prepare("SELECT * FROM product_categories WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'category' => $category]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Get all categories
$stmt = $db->query("SELECT c.*, (SELECT COUNT(*) FROM plans WHERE category = c.name) as product_count FROM product_categories c ORDER BY display_order, name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Categories';
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
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .category-card:hover {
            border-color: rgba(0, 212, 255, 0.5);
            box-shadow: 0 5px 20px rgba(0, 212, 255, 0.1);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .category-icon {
            font-size: 2rem;
            color: #00d4ff;
            margin-bottom: 10px;
        }

        .category-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
        }

        .category-description {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 15px;
            min-height: 40px;
        }

        .category-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .category-count {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .category-actions {
            display: flex;
            gap: 10px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
        }

        .status-inactive {
            background: rgba(255, 68, 68, 0.2);
            color: #ff4444;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: #0a0e27;
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(0, 212, 255, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-title {
            font-size: 1.5rem;
            color: #00d4ff;
        }

        .close-modal {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            color: #fff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 0.95rem;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn-submit {
            background: linear-gradient(135deg, #00d4ff, #0099ff);
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: #00ff88;
        }

        .alert-error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: #ff4444;
        }

        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 20px;
                padding: 20px;
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
                <h1>Product Categories</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>

            <div id="alert-container"></div>

            <?php if (empty($categories)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No categories found. Create your first category to organize your products.
                </div>
            <?php else: ?>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-header">
                                <span class="status-badge status-<?php echo $category['status']; ?>">
                                    <?php echo ucfirst($category['status']); ?>
                                </span>
                            </div>

                            <?php if ($category['icon']): ?>
                                <div class="category-icon">
                                    <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                                </div>
                            <?php endif; ?>

                            <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                            <div class="category-description"><?php echo htmlspecialchars($category['description']); ?></div>

                            <div class="category-meta">
                                <span class="category-count">
                                    <i class="fas fa-box"></i> <?php echo $category['product_count']; ?> products
                                </span>
                                <div class="category-actions">
                                    <button class="btn btn-sm btn-primary" onclick="editCategory(<?php echo $category['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add Category</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>

            <form id="categoryForm" onsubmit="saveCategory(event)">
                <input type="hidden" id="categoryId" name="id">

                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Web Hosting">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Brief description of this category..."></textarea>
                </div>

                <div class="form-group">
                    <label for="icon">Icon (Font Awesome class)</label>
                    <input type="text" id="icon" name="icon" placeholder="e.g., fas fa-server">
                    <small style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">
                        Visit <a href="https://fontawesome.com/icons" target="_blank" style="color: #00d4ff;">fontawesome.com</a> to find icons
                    </small>
                </div>

                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" value="0" placeholder="0">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Save Category</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function editCategory(id) {
            fetch('manage-categories.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('modalTitle').textContent = 'Edit Category';
                    document.getElementById('categoryId').value = data.category.id;
                    document.getElementById('name').value = data.category.name;
                    document.getElementById('description').value = data.category.description || '';
                    document.getElementById('icon').value = data.category.icon || '';
                    document.getElementById('display_order').value = data.category.display_order;
                    document.getElementById('status').value = data.category.status;
                    document.getElementById('categoryModal').style.display = 'block';
                }
            });
        }

        function saveCategory(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const id = document.getElementById('categoryId').value;
            formData.append('action', id ? 'edit' : 'add');

            fetch('manage-categories.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred: ' + error, 'error');
            });
        }

        function deleteCategory(id, name) {
            if (!confirm('Are you sure you want to delete the category "' + name + '"?')) {
                return;
            }

            fetch('manage-categories.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred: ' + error, 'error');
            });
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-' + type;
            alertDiv.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;

            const container = document.getElementById('alert-container');
            container.innerHTML = '';
            container.appendChild(alertDiv);

            setTimeout(() => alertDiv.remove(), 5000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
