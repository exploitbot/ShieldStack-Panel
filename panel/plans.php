<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();

// Get all active plans grouped by category
$plansStmt = $db->prepare("SELECT * FROM plans WHERE status = 'active' AND (hidden IS NULL OR hidden = 0) ORDER BY display_order, category, price ASC");
$plansStmt->execute();
$allPlans = $plansStmt->fetchAll();

// Get category information from product_categories table if it exists
$categoryInfo = [];
try {
    $categoriesStmt = $db->query("SELECT name, description, icon, display_order FROM product_categories WHERE status = 'active' ORDER BY display_order, name");
    $categories = $categoriesStmt->fetchAll();
    foreach ($categories as $cat) {
        $categoryInfo[$cat['name']] = [
            'name' => $cat['name'],
            'icon' => $cat['icon'] ?: '📦',
            'description' => $cat['description']
        ];
    }
} catch (PDOException $e) {
    // Table doesn't exist yet, use default categories
}

// Fallback to default categories if not in database
$defaultCategories = [
    'hosting' => ['name' => 'Web Hosting', 'icon' => '🌐'],
    'vps' => ['name' => 'VPS Servers', 'icon' => '🖥️'],
    'dedicated' => ['name' => 'Dedicated Servers', 'icon' => '💻'],
    'ssl' => ['name' => 'SSL Certificates', 'icon' => '🔒'],
    'domains' => ['name' => 'Domain Names', 'icon' => '🌍'],
    'other' => ['name' => 'Other Services', 'icon' => '📦']
];

// Merge with defaults
foreach ($defaultCategories as $key => $value) {
    if (!isset($categoryInfo[$key])) {
        $categoryInfo[$key] = $value;
    }
}

// Group plans by category
$plansByCategory = [];
foreach ($allPlans as $plan) {
    $category = $plan['category'] ?? 'other';
    if (!isset($plansByCategory[$category])) {
        $plansByCategory[$category] = [];
    }
    $plansByCategory[$category][] = $plan;
}

// Handle order submission
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Plans - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .category-section {
            margin-bottom: 50px;
        }
        .category-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
        }
        .category-icon {
            font-size: 32px;
        }
        .category-title {
            font-size: 24px;
            color: var(--primary-color);
        }
        .plan-feature-list {
            list-style: none;
            margin: 20px 0;
            padding: 0;
        }
        .plan-feature-list li {
            padding: 8px 0;
            color: var(--text-primary);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .plan-feature-list li::before {
            content: "✓";
            color: var(--success);
            font-weight: bold;
            font-size: 16px;
        }
        .plan-specs {
            background: var(--background);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .plan-spec-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .plan-spec-item strong {
            color: var(--text-primary);
        }
        @media (max-width: 768px) {
            .category-title {
                font-size: 20px;
            }
            .category-icon {
                font-size: 24px;
            }
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
                    <h1 class="page-title">Browse Plans</h1>
                    <p class="page-subtitle">Choose the perfect plan for your needs</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php foreach ($plansByCategory as $category => $plans): ?>
                    <div class="category-section">
                        <div class="category-header">
                            <span class="category-icon"><?php echo $categoryInfo[$category]['icon'] ?? '📦'; ?></span>
                            <h2 class="category-title"><?php echo $categoryInfo[$category]['name'] ?? ucfirst($category); ?></h2>
                        </div>

                        <div class="plans-grid">
                            <?php foreach ($plans as $plan): ?>
                                <?php $features = json_decode($plan['features'], true); ?>
                        <div class="plan-card">
                            <div class="plan-header">
                                <h3 class="plan-name"><?php echo htmlspecialchars($plan['name']); ?></h3>
                                <div class="plan-price">
                                    <?php if ($plan['price'] > 0): ?>
                                        $<?php echo number_format($plan['price'], 2); ?>
                                        <span>/<?php echo htmlspecialchars($plan['billing_cycle']); ?></span>
                                    <?php else: ?>
                                        <span>Contact us for pricing</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                                    <p class="plan-description"><?php echo htmlspecialchars($plan['description']); ?></p>

                                    <!-- Plan Specifications -->
                                    <?php if ($category === 'hosting' || $category === 'vps' || $category === 'dedicated'): ?>
                                        <div class="plan-specs">
                                            <?php if ($plan['disk_space']): ?>
                                                <div class="plan-spec-item">
                                                    <span>Disk Space:</span>
                                                    <strong><?php echo htmlspecialchars($plan['disk_space']); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($plan['bandwidth']): ?>
                                                <div class="plan-spec-item">
                                                    <span>Bandwidth:</span>
                                                    <strong><?php echo htmlspecialchars($plan['bandwidth']); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($plan['databases']): ?>
                                                <div class="plan-spec-item">
                                                    <span>Databases:</span>
                                                    <strong><?php echo $plan['databases'] == 999 ? 'Unlimited' : $plan['databases']; ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($plan['email_accounts']): ?>
                                                <div class="plan-spec-item">
                                                    <span>Email Accounts:</span>
                                                    <strong><?php echo $plan['email_accounts'] == 999 ? 'Unlimited' : $plan['email_accounts']; ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($plan['subdomains']): ?>
                                                <div class="plan-spec-item">
                                                    <span>Subdomains:</span>
                                                    <strong><?php echo $plan['subdomains'] == 999 ? 'Unlimited' : $plan['subdomains']; ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Plan Features -->
                                    <?php if ($features && is_array($features) && count($features) > 0): ?>
                                        <ul class="plan-feature-list">
                                            <?php foreach ($features as $feature): ?>
                                                <li><?php echo htmlspecialchars($feature); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <!-- Additional Features Badges -->
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin: 15px 0;">
                                        <?php if ($plan['ssl_certificates']): ?>
                                            <span class="badge badge-success">Free SSL</span>
                                        <?php endif; ?>
                                        <?php if ($plan['daily_backups']): ?>
                                            <span class="badge badge-info">Daily Backups</span>
                                        <?php endif; ?>
                                        <?php if ($plan['support_level'] === 'priority'): ?>
                                            <span class="badge badge-warning">Priority Support</span>
                                        <?php endif; ?>
                                    </div>

                                    <button onclick="orderPlan(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars(addslashes($plan['name'])); ?>')" class="btn btn-primary">
                                        Order Now
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($plansByCategory)): ?>
                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 60px 20px;">
                            <svg width="80" height="80" viewBox="0 0 60 60" fill="none" style="margin-bottom: 20px;">
                                <rect x="15" y="15" width="30" height="30" stroke="var(--border)" stroke-width="2" fill="rgba(0,212,255,0.05)"/>
                            </svg>
                            <h3 style="color: var(--text-primary); margin-bottom: 10px;">No Plans Available</h3>
                            <p style="color: var(--text-secondary);">
                                There are currently no plans available. Please check back later.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">Order <span id="planName"></span></h2>
            <form method="POST">
                <input type="hidden" name="plan_id" id="planId">
                <div class="form-group">
                    <label for="domain">Domain Name (optional)</label>
                    <input type="text" id="domain" name="domain" placeholder="example.com">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="order_plan" class="btn btn-primary" style="flex: 1;">Confirm Order</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function orderPlan(planId, planName) {
            document.getElementById('planId').value = planId;
            document.getElementById('planName').textContent = planName;
            document.getElementById('orderModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
            }
        }
    </script>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>
