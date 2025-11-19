<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

// Get all customer services with full plan details
$servicesStmt = $db->prepare("
    SELECT s.*, 
           p.name as plan_name, p.type, p.price, p.billing_cycle, p.description, p.category,
           p.disk_space, p.bandwidth, p.`databases`, p.email_accounts, p.subdomains,
           p.ftp_accounts, p.ssl_certificates, p.daily_backups, p.support_level, p.features
    FROM services s
    JOIN plans p ON s.plan_id = p.id
    WHERE s.customer_id = ?
    ORDER BY s.created_at DESC
");
$servicesStmt->execute([$customerId]);
$services = $servicesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Services - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .service-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .service-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .service-title h3 {
            color: var(--text-primary);
            font-size: 20px;
            margin-bottom: 5px;
        }
        .service-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .service-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .info-item {
            background: var(--background);
            padding: 12px;
            border-radius: 6px;
        }
        .info-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: var(--text-primary);
            font-weight: 600;
        }
        .plan-features-compact {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .feature-item strong {
            color: var(--text-primary);
        }
        .expiry-warning {
            background: rgba(255, 170, 0, 0.1);
            border: 1px solid var(--warning);
            color: var(--warning);
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 13px;
            margin-top: 15px;
        }
        .expiry-danger {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
        }
        .suspension-notice {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .service-header {
                flex-direction: column;
            }
            .service-info-grid {
                grid-template-columns: 1fr;
            }
            .plan-features-compact {
                grid-template-columns: 1fr;
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
                    <h1 class="page-title">My Services</h1>
                    <p class="page-subtitle">Manage your hosting services and subscriptions</p>
                </div>

                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $service): ?>
                        <?php 
                        $features = json_decode($service['features'], true);
                        $daysUntilExpiry = null;
                        if ($service['expiry_date']) {
                            $expiryTime = strtotime($service['expiry_date']);
                            $daysUntilExpiry = floor(($expiryTime - time()) / (60 * 60 * 24));
                        }
                        ?>
                        <div class="service-card">
                            <!-- Suspension Notice -->
                            <?php if ($service['suspended']): ?>
                                <div class="suspension-notice">
                                    <strong>⚠ Service Suspended</strong><br>
                                    <?php if ($service['suspension_reason']): ?>
                                        Reason: <?php echo htmlspecialchars($service['suspension_reason']); ?>
                                    <?php endif; ?>
                                    <br><small>Please contact support to resolve this issue.</small>
                                </div>
                            <?php endif; ?>

                            <div class="service-header">
                                <div class="service-title">
                                    <h3><?php echo htmlspecialchars($service['plan_name']); ?></h3>
                                    <div class="service-meta">
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars(ucfirst($service['category'])); ?>
                                        </span>
                                        <span class="badge badge-<?php
                                            echo $service['suspended'] ? 'error' : 
                                                ($service['status'] === 'active' ? 'success' :
                                                ($service['status'] === 'pending' ? 'warning' : 'error'));
                                        ?>">
                                            <?php echo htmlspecialchars(ucfirst($service['status'])); ?>
                                        </span>
                                        <?php if ($service['auto_renew']): ?>
                                            <span class="badge badge-success">Auto-Renew: ON</span>
                                        <?php else: ?>
                                            <span class="badge badge-error">Auto-Renew: OFF</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 24px; color: var(--primary-color); font-weight: 700;">
                                        $<?php echo number_format($service['price'], 2); ?>
                                    </div>
                                    <div style="font-size: 13px; color: var(--text-secondary);">
                                        per <?php echo htmlspecialchars($service['billing_cycle']); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Information Grid -->
                            <div class="service-info-grid">
                                <?php if ($service['domain']): ?>
                                    <div class="info-item">
                                        <div class="info-label">Domain</div>
                                        <div class="info-value"><?php echo htmlspecialchars($service['domain']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <div class="info-label">Start Date</div>
                                    <div class="info-value">
                                        <?php echo $service['start_date'] ? date('M d, Y', strtotime($service['start_date'])) : 'N/A'; ?>
                                    </div>
                                </div>

                                <?php if ($service['renewal_date']): ?>
                                    <div class="info-item">
                                        <div class="info-label">Next Renewal</div>
                                        <div class="info-value">
                                            <?php echo date('M d, Y', strtotime($service['renewal_date'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($service['expiry_date']): ?>
                                    <div class="info-item">
                                        <div class="info-label">Expiry Date</div>
                                        <div class="info-value">
                                            <?php echo date('M d, Y', strtotime($service['expiry_date'])); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Expiry Warning -->
                            <?php if ($daysUntilExpiry !== null && !$service['auto_renew']): ?>
                                <?php if ($daysUntilExpiry < 0): ?>
                                    <div class="expiry-warning expiry-danger">
                                        ⚠ This service has expired. Please renew to continue using it.
                                    </div>
                                <?php elseif ($daysUntilExpiry <= 7): ?>
                                    <div class="expiry-warning expiry-danger">
                                        ⚠ This service expires in <?php echo $daysUntilExpiry; ?> day(s). Please renew soon!
                                    </div>
                                <?php elseif ($daysUntilExpiry <= 30): ?>
                                    <div class="expiry-warning">
                                        ⚠ This service expires in <?php echo $daysUntilExpiry; ?> days.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Plan Features -->
                            <?php if ($service['category'] === 'hosting' || $service['category'] === 'vps' || $service['category'] === 'dedicated'): ?>
                                <div style="margin-top: 20px;">
                                    <h4 style="color: var(--text-primary); margin-bottom: 12px; font-size: 16px;">Plan Resources</h4>
                                    <div class="plan-features-compact">
                                        <?php if ($service['disk_space']): ?>
                                            <div class="feature-item">
                                                <span>💾</span>
                                                <div><strong><?php echo htmlspecialchars($service['disk_space']); ?></strong> Disk</div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($service['bandwidth']): ?>
                                            <div class="feature-item">
                                                <span>📊</span>
                                                <div><strong><?php echo htmlspecialchars($service['bandwidth']); ?></strong> Bandwidth</div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($service['databases']): ?>
                                            <div class="feature-item">
                                                <span>🗄️</span>
                                                <div><strong><?php echo $service['databases'] == 999 ? 'Unlimited' : $service['databases']; ?></strong> Databases</div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($service['email_accounts']): ?>
                                            <div class="feature-item">
                                                <span>📧</span>
                                                <div><strong><?php echo $service['email_accounts'] == 999 ? 'Unlimited' : $service['email_accounts']; ?></strong> Email</div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($service['subdomains']): ?>
                                            <div class="feature-item">
                                                <span>🌐</span>
                                                <div><strong><?php echo $service['subdomains'] == 999 ? 'Unlimited' : $service['subdomains']; ?></strong> Subdomains</div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($service['ftp_accounts']): ?>
                                            <div class="feature-item">
                                                <span>📁</span>
                                                <div><strong><?php echo $service['ftp_accounts'] == 999 ? 'Unlimited' : $service['ftp_accounts']; ?></strong> FTP</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Additional Features -->
                            <?php if ($features && is_array($features) && count($features) > 0): ?>
                                <div style="margin-top: 20px;">
                                    <h4 style="color: var(--text-primary); margin-bottom: 12px; font-size: 16px;">Included Features</h4>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <?php if ($service['ssl_certificates']): ?>
                                            <span class="badge badge-success">🔒 Free SSL</span>
                                        <?php endif; ?>
                                        <?php if ($service['daily_backups']): ?>
                                            <span class="badge badge-info">💾 Daily Backups</span>
                                        <?php endif; ?>
                                        <span class="badge badge-info">
                                            📞 <?php echo ucfirst($service['support_level']); ?> Support
                                        </span>
                                        <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                            <span class="badge badge-info">✓ <?php echo htmlspecialchars($feature); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap;">
                                <?php if ($service['status'] === 'active' && !$service['suspended']): ?>
                                    <button class="btn btn-primary" style="flex: 1;">Manage Service</button>
                                <?php endif; ?>
                                <?php if (!$service['auto_renew'] && $daysUntilExpiry <= 30): ?>
                                    <button class="btn btn-success" style="flex: 1;">Renew Now</button>
                                <?php endif; ?>
                                <button class="btn btn-secondary" style="flex: 1;">View Invoice</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 60px 20px;">
                            <svg width="80" height="80" viewBox="0 0 60 60" fill="none" style="margin-bottom: 20px;">
                                <rect x="15" y="15" width="30" height="30" stroke="var(--border)" stroke-width="2" fill="rgba(0,212,255,0.05)"/>
                            </svg>
                            <h3 style="color: var(--text-primary); margin-bottom: 10px;">No Services Yet</h3>
                            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                                You don't have any services yet. Browse our plans to get started!
                            </p>
                            <a href="/panel/plans.php" class="btn btn-primary">Browse Plans</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>
