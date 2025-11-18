<?php
require_once 'includes/auth.php';
$auth = new Auth();
$auth->requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domains - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Domain Management</h1>
                    <p class="page-subtitle">Manage your domains and DNS settings</p>
                </div>
                <div class="card">
                    <div class="card-body">
                        <p style="color: var(--text-secondary); text-align: center; padding: 40px;">
                            Domain management coming soon! Contact support for domain assistance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
