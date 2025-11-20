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
    <title>Knowledge Base - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            <div class="content-wrapper">
                <div class="page-header">
                    <h1 class="page-title">Knowledge Base</h1>
                    <p class="page-subtitle">Find answers to common questions</p>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h3 style="margin-bottom: 15px;">Getting Started</h3>
                        <ul style="color: var(--text-secondary); line-height: 2;">
                            <li><a href="#" style="color: var(--primary-color);">How to order a hosting plan</a></li>
                            <li><a href="#" style="color: var(--primary-color);">Setting up your domain</a></li>
                            <li><a href="#" style="color: var(--primary-color);">Managing your services</a></li>
                            <li><a href="#" style="color: var(--primary-color);">Payment methods</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
