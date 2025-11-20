<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$auth = new Auth();
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settings = [
        // SMTP Configuration
        'smtp_enabled' => isset($_POST['smtp_enabled']) ? 1 : 0,
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? 587,
        'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_password' => $_POST['smtp_password'] ?? '',
        
        // Email Identity
        'from_email' => $_POST['from_email'] ?? '',
        'from_name' => $_POST['from_name'] ?? 'ShieldStack',
        'reply_to_email' => $_POST['reply_to_email'] ?? '',
        
        // Ticket Notifications
        'notify_ticket_created' => isset($_POST['notify_ticket_created']) ? 1 : 0,
        'notify_ticket_reply_admin' => isset($_POST['notify_ticket_reply_admin']) ? 1 : 0,
        'notify_ticket_reply_client' => isset($_POST['notify_ticket_reply_client']) ? 1 : 0,
        'notify_ticket_status_change' => isset($_POST['notify_ticket_status_change']) ? 1 : 0,
        
        // Invoice Notifications
        'notify_invoice_created' => isset($_POST['notify_invoice_created']) ? 1 : 0,
        'notify_invoice_paid' => isset($_POST['notify_invoice_paid']) ? 1 : 0,
        'notify_invoice_overdue' => isset($_POST['notify_invoice_overdue']) ? 1 : 0,
        'notify_invoice_reminder' => isset($_POST['notify_invoice_reminder']) ? 1 : 0,
        
        // Email Template Settings
        'email_header_color' => $_POST['email_header_color'] ?? '#667eea',
        'email_button_color' => $_POST['email_button_color'] ?? '#667eea',
        'email_logo_url' => $_POST['email_logo_url'] ?? '',
        'email_footer_text' => $_POST['email_footer_text'] ?? '',
        
        // Admin Notification Email
        'admin_notification_email' => $_POST['admin_notification_email'] ?? ''
    ];

    try {
        $db->beginTransaction();

        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO system_settings (`key`, `value`)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE `value` = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }

        $db->commit();
        $success = 'Email settings saved successfully!';
    } catch (PDOException $e) {
        $db->rollBack();
        $error = 'Failed to save settings: ' . $e->getMessage();
    }
}

// Test email connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_connection'])) {
    $testEmail = $_POST['test_email'] ?? '';
    $testType = $_POST['test_type'] ?? 'basic';

    if ($testEmail) {
        try {
            require '../vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Get current settings
            $stmt = $db->query("SELECT `key`, `value` FROM system_settings WHERE `key` LIKE 'smtp_%' OR `key` LIKE 'from_%' OR `key` LIKE 'email_%'");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key']] = $row['value'];
            }

            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'] ?? '';
            $mail->SMTPAuth = true;
            $mail->Username = $settings['smtp_username'] ?? '';
            $mail->Password = $settings['smtp_password'] ?? '';
            $mail->SMTPSecure = $settings['smtp_encryption'] ?? 'tls';
            $mail->Port = $settings['smtp_port'] ?? 587;

            $mail->setFrom(
                $settings['from_email'] ?? '', 
                $settings['from_name'] ?? 'ShieldStack'
            );
            $mail->addAddress($testEmail);

            $mail->isHTML(true);
            
            // Different test types
            if ($testType === 'ticket_created') {
                $mail->Subject = '[TEST] New Support Ticket #12345';
                $mail->Body = getTestEmailTemplate('ticket_created', $settings);
            } elseif ($testType === 'invoice_created') {
                $mail->Subject = '[TEST] New Invoice #INV-20250131';
                $mail->Body = getTestEmailTemplate('invoice_created', $settings);
            } else {
                $mail->Subject = 'Test Email from ShieldStack';
                $mail->Body = getTestEmailTemplate('basic', $settings);
            }

            $mail->send();
            $success = 'Test email sent successfully to ' . htmlspecialchars($testEmail) . '! Check your inbox.';
        } catch (Exception $e) {
            $error = 'Failed to send test email: ' . $e->getMessage();
        }
    } else {
        $error = 'Please provide a test email address.';
    }
}

// Load current settings
$stmt = $db->query("SELECT `key`, `value` FROM system_settings");
$currentSettings = [];
while ($row = $stmt->fetch()) {
    $currentSettings[$row['key']] = $row['value'];
}

function getSetting($key, $default = '') {
    global $currentSettings;
    return $currentSettings[$key] ?? $default;
}

function getTestEmailTemplate($type, $settings) {
    $headerColor = $settings['email_header_color'] ?? '#667eea';
    $buttonColor = $settings['email_button_color'] ?? '#667eea';
    
    if ($type === 'ticket_created') {
        $content = "
            <p>This is a test email showing how ticket creation notifications will look.</p>
            <table style='width: 100%; margin: 20px 0; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>Ticket ID:</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>#12345</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>Subject:</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>Test Support Request</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>Priority:</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>MEDIUM</td>
                </tr>
            </table>
            <p><strong>Message:</strong></p>
            <div style='background: #f9f9f9; padding: 20px; border-radius: 5px;'>This is a sample ticket message.</div>
        ";
        $actionButton = "<table role='presentation' style='margin: 30px auto;'>
            <tr>
                <td style='border-radius: 5px; background: {$buttonColor};'>
                    <a href='#' style='border: none; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; font-weight: bold;'>View Ticket</a>
                </td>
            </tr>
        </table>";
    } elseif ($type === 'invoice_created') {
        $content = "
            <p>This is a test email showing how invoice notifications will look.</p>
            <table style='width: 100%; margin: 20px 0; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>Invoice Number:</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>#INV-20250131</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>Amount:</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; font-size: 20px; color: {$buttonColor};'><strong>$99.99</strong></td>
                </tr>
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;'>Due Date:</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>February 15, 2025</td>
                </tr>
            </table>
        ";
        $actionButton = "<table role='presentation' style='margin: 30px auto;'>
            <tr>
                <td style='border-radius: 5px; background: {$buttonColor};'>
                    <a href='#' style='border: none; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; font-weight: bold;'>View & Pay Invoice</a>
                </td>
            </tr>
        </table>";
    } else {
        $content = "
            <p>Congratulations! Your SMTP configuration is working correctly.</p>
            <div style='background: #f0f7ff; padding: 20px; border-left: 4px solid {$buttonColor}; border-radius: 5px; margin: 20px 0;'>
                <p style='margin: 0;'><strong>SMTP Configuration Details:</strong></p>
                <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                    <li>Host: " . ($settings['smtp_host'] ?? 'Not configured') . "</li>
                    <li>Port: " . ($settings['smtp_port'] ?? 'Not configured') . "</li>
                    <li>Encryption: " . strtoupper($settings['smtp_encryption'] ?? 'Not configured') . "</li>
                    <li>From: " . ($settings['from_email'] ?? 'Not configured') . "</li>
                </ul>
            </div>
        ";
        $actionButton = "";
    }
    
    return "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background-color: #f4f4f4;'>
        <table role='presentation' style='width: 100%; border-collapse: collapse;'>
            <tr>
                <td align='center' style='padding: 40px 0;'>
                    <table role='presentation' style='width: 600px; border-collapse: collapse; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                        <tr>
                            <td style='padding: 40px; background: {$headerColor}; border-radius: 10px 10px 0 0; text-align: center;'>
                                <h1 style='margin: 0; color: white; font-size: 28px;'>Test Email</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 40px; color: #333; font-size: 16px; line-height: 1.6;'>
                                {$content}
                                {$actionButton}
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px; background: #f9f9f9; border-radius: 0 0 10px 10px; text-align: center; color: #666; font-size: 14px;'>
                                <p style='margin: 0 0 10px 0;'>This is an automated test email from ShieldStack</p>
                                <p style='margin: 0; color: #999;'>© " . date('Y') . " ShieldStack. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Settings - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .tab {
            padding: 15px 25px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.3s;
        }
        
        .tab:hover {
            color: var(--primary-color);
            background: var(--hover-bg);
        }
        
        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .settings-section {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .settings-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="number"],
        .form-group input[type="color"],
        .form-group input[type="url"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: var(--text-secondary);
            font-size: 12px;
        }
        
        .form-group input[type="color"] {
            height: 45px;
            cursor: pointer;
        }
        
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .checkbox-card {
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .checkbox-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }
        
        .checkbox-card.active {
            border-color: var(--primary-color);
            background: var(--info-bg);
        }
        
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            margin-top: 2px;
        }
        
        .checkbox-content {
            flex: 1;
        }
        
        .checkbox-content strong {
            display: block;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        
        .checkbox-content small {
            color: var(--text-secondary);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .status-badge.enabled {
            background: var(--success-bg);
            color: var(--success);
        }
        
        .status-badge.disabled {
            background: var(--error-bg);
            color: var(--error);
        }
        
        .test-section {
            background: var(--info-bg);
            border: 2px solid var(--info);
            border-radius: 10px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .test-section h3 {
            margin-top: 0;
            color: var(--info);
            font-size: 18px;
        }
        
        .test-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: flex-end;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
        }
        
        .color-preview {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            border: 2px solid var(--border-color);
            vertical-align: middle;
            margin-left: 10px;
        }
        
        .notification-category {
            margin-bottom: 30px;
        }
        
        .notification-category h4 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: var(--text-primary);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1><i class="fas fa-envelope"></i> Email Settings & Configuration</h1>
            <?php if (getSetting('smtp_enabled')): ?>
                <span class="status-badge enabled"><i class="fas fa-circle"></i> Email System Enabled</span>
            <?php else: ?>
                <span class="status-badge disabled"><i class="fas fa-circle"></i> Email System Disabled</span>
            <?php endif; ?>
        </div>

        <div class="container">
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

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="switchTab('smtp')">
                    <i class="fas fa-server"></i> SMTP Configuration
                </button>
                <button class="tab" onclick="switchTab('identity')">
                    <i class="fas fa-id-card"></i> Email Identity
                </button>
                <button class="tab" onclick="switchTab('notifications')">
                    <i class="fas fa-bell"></i> Notifications
                </button>
                <button class="tab" onclick="switchTab('templates')">
                    <i class="fas fa-paint-brush"></i> Templates
                </button>
                <button class="tab" onclick="switchTab('test')">
                    <i class="fas fa-vial"></i> Test & Verify
                </button>
            </div>

            <form method="POST" id="settingsForm">
                <!-- SMTP Configuration Tab -->
                <div id="smtp-tab" class="tab-content active">
                    <div class="settings-section">
                        <h3><i class="fas fa-server"></i> SMTP Server Settings</h3>
                        
                        <div class="checkbox-card" onclick="toggleCheckbox('smtp_enabled')">
                            <label class="checkbox-label">
                                <input type="checkbox" name="smtp_enabled" id="smtp_enabled" <?php echo getSetting('smtp_enabled') ? 'checked' : ''; ?>>
                                <div class="checkbox-content">
                                    <strong>Enable SMTP Email Service</strong>
                                    <small>Turn on to send automated emails for tickets, invoices, and notifications</small>
                                </div>
                            </label>
                        </div>

                        <div class="form-row" style="margin-top: 20px;">
                            <div class="form-group">
                                <label for="smtp_host">SMTP Host *</label>
                                <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars(getSetting('smtp_host')); ?>" required>
                                <small>Examples: smtp.gmail.com, smtp.office365.com, mail.yourdomain.com</small>
                            </div>

                            <div class="form-group">
                                <label for="smtp_port">SMTP Port *</label>
                                <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars(getSetting('smtp_port', '587')); ?>" required>
                                <small>Common: 587 (TLS), 465 (SSL), 25 (unencrypted)</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp_encryption">Encryption Method *</label>
                                <select id="smtp_encryption" name="smtp_encryption" required>
                                    <option value="tls" <?php echo getSetting('smtp_encryption') === 'tls' ? 'selected' : ''; ?>>TLS (Recommended)</option>
                                    <option value="ssl" <?php echo getSetting('smtp_encryption') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="none" <?php echo getSetting('smtp_encryption') === 'none' ? 'selected' : ''; ?>>None (Not Recommended)</option>
                                </select>
                                <small>TLS is recommended for most email providers</small>
                            </div>

                            <div class="form-group">
                                <label for="smtp_username">SMTP Username *</label>
                                <input type="text" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars(getSetting('smtp_username')); ?>" required>
                                <small>Usually your full email address</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp_password">SMTP Password *</label>
                                <input type="password" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars(getSetting('smtp_password')); ?>" placeholder="<?php echo getSetting('smtp_password') ? '••••••••' : ''; ?>">
                                <small>For Gmail, use an App Password. For Office365, use your account password</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Identity Tab -->
                <div id="identity-tab" class="tab-content">
                    <div class="settings-section">
                        <h3><i class="fas fa-id-card"></i> Sender Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="from_email">From Email Address *</label>
                                <input type="email" id="from_email" name="from_email" value="<?php echo htmlspecialchars(getSetting('from_email')); ?>" required>
                                <small>This email address will appear as the sender</small>
                            </div>

                            <div class="form-group">
                                <label for="from_name">From Name *</label>
                                <input type="text" id="from_name" name="from_name" value="<?php echo htmlspecialchars(getSetting('from_name', 'ShieldStack')); ?>" required>
                                <small>Name displayed to email recipients</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="reply_to_email">Reply-To Email</label>
                                <input type="email" id="reply_to_email" name="reply_to_email" value="<?php echo htmlspecialchars(getSetting('reply_to_email')); ?>">
                                <small>Where replies should be sent (optional, defaults to From email)</small>
                            </div>

                            <div class="form-group">
                                <label for="admin_notification_email">Admin Notification Email</label>
                                <input type="email" id="admin_notification_email" name="admin_notification_email" value="<?php echo htmlspecialchars(getSetting('admin_notification_email')); ?>">
                                <small>Primary email for admin notifications (optional, defaults to all admins)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div id="notifications-tab" class="tab-content">
                    <div class="settings-section">
                        <h3><i class="fas fa-bell"></i> Notification Preferences</h3>
                        <p style="margin-bottom: 25px; color: var(--text-secondary);">Choose which events trigger automatic email notifications</p>

                        <div class="notification-category">
                            <h4><i class="fas fa-ticket-alt"></i> Ticket Notifications</h4>
                            <div class="checkbox-group">
                                <div class="checkbox-card <?php echo getSetting('notify_ticket_created') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_ticket_created')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_ticket_created" id="notify_ticket_created" <?php echo getSetting('notify_ticket_created') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-plus-circle"></i> New Ticket Created</strong>
                                            <small>Notify admins when a client creates a new support ticket</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="checkbox-card <?php echo getSetting('notify_ticket_reply_admin') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_ticket_reply_admin')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_ticket_reply_admin" id="notify_ticket_reply_admin" <?php echo getSetting('notify_ticket_reply_admin', '1') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-reply"></i> Admin Reply to Ticket</strong>
                                            <small>Notify clients when an admin responds to their ticket</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="checkbox-card <?php echo getSetting('notify_ticket_reply_client') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_ticket_reply_client')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_ticket_reply_client" id="notify_ticket_reply_client" <?php echo getSetting('notify_ticket_reply_client', '1') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-comment"></i> Client Reply to Ticket</strong>
                                            <small>Notify admins when a client adds a reply to their ticket</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="checkbox-card <?php echo getSetting('notify_ticket_status_change') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_ticket_status_change')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_ticket_status_change" id="notify_ticket_status_change" <?php echo getSetting('notify_ticket_status_change') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-exchange-alt"></i> Ticket Status Changed</strong>
                                            <small>Notify clients when ticket status changes (open, resolved, closed)</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="notification-category">
                            <h4><i class="fas fa-file-invoice-dollar"></i> Invoice Notifications</h4>
                            <div class="checkbox-group">
                                <div class="checkbox-card <?php echo getSetting('notify_invoice_created') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_invoice_created')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_invoice_created" id="notify_invoice_created" <?php echo getSetting('notify_invoice_created') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-file-invoice"></i> New Invoice Created</strong>
                                            <small>Notify clients when a new invoice is generated for their account</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="checkbox-card <?php echo getSetting('notify_invoice_paid') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_invoice_paid')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_invoice_paid" id="notify_invoice_paid" <?php echo getSetting('notify_invoice_paid') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-check-circle"></i> Invoice Paid</strong>
                                            <small>Notify clients and admins when an invoice is marked as paid</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="checkbox-card <?php echo getSetting('notify_invoice_overdue') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_invoice_overdue')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_invoice_overdue" id="notify_invoice_overdue" <?php echo getSetting('notify_invoice_overdue') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-exclamation-triangle"></i> Invoice Overdue</strong>
                                            <small>Send reminder emails when invoices become overdue</small>
                                        </div>
                                    </label>
                                </div>

                                <div class="checkbox-card <?php echo getSetting('notify_invoice_reminder') ? 'active' : ''; ?>" onclick="toggleCheckbox('notify_invoice_reminder')">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="notify_invoice_reminder" id="notify_invoice_reminder" <?php echo getSetting('notify_invoice_reminder') ? 'checked' : ''; ?>>
                                        <div class="checkbox-content">
                                            <strong><i class="fas fa-clock"></i> Invoice Due Soon</strong>
                                            <small>Send reminder 3 days before invoice due date</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates Tab -->
                <div id="templates-tab" class="tab-content">
                    <div class="settings-section">
                        <h3><i class="fas fa-paint-brush"></i> Email Template Customization</h3>
                        <p style="margin-bottom: 25px; color: var(--text-secondary);">Customize the appearance of your email notifications</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_header_color">Header Background Color</label>
                                <input type="color" id="email_header_color" name="email_header_color" value="<?php echo htmlspecialchars(getSetting('email_header_color', '#667eea')); ?>">
                                <small>Color for email header background</small>
                            </div>

                            <div class="form-group">
                                <label for="email_button_color">Button Color</label>
                                <input type="color" id="email_button_color" name="email_button_color" value="<?php echo htmlspecialchars(getSetting('email_button_color', '#667eea')); ?>">
                                <small>Color for action buttons in emails</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email_logo_url">Logo URL (Optional)</label>
                                <input type="url" id="email_logo_url" name="email_logo_url" value="<?php echo htmlspecialchars(getSetting('email_logo_url')); ?>" placeholder="https://yourdomain.com/logo.png">
                                <small>URL to your company logo for email headers (leave blank to use text)</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email_footer_text">Custom Footer Text</label>
                            <textarea id="email_footer_text" name="email_footer_text" placeholder="Add custom footer text for your emails..."><?php echo htmlspecialchars(getSetting('email_footer_text')); ?></textarea>
                            <small>Additional text to display in email footers (optional)</small>
                        </div>

                        <div style="background: var(--info-bg); padding: 20px; border-radius: 8px; margin-top: 20px;">
                            <h4 style="margin: 0 0 10px 0; color: var(--info);"><i class="fas fa-info-circle"></i> Preview Colors</h4>
                            <p style="margin: 0; font-size: 14px;">
                                Header: <span class="color-preview" style="background: <?php echo getSetting('email_header_color', '#667eea'); ?>;"></span>
                                Button: <span class="color-preview" style="background: <?php echo getSetting('email_button_color', '#667eea'); ?>;"></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Test Tab -->
                <div id="test-tab" class="tab-content">
                    <div class="test-section">
                        <h3><i class="fas fa-vial"></i> Test Email Configuration</h3>
                        <p style="margin-bottom: 20px;">Send test emails to verify your SMTP configuration and preview email templates</p>

                        <div class="test-form">
                            <div class="form-group">
                                <label for="test_email">Recipient Email Address</label>
                                <input type="email" id="test_email" name="test_email" placeholder="your@email.com" required>
                            </div>

                            <div class="form-group">
                                <label for="test_type">Email Type</label>
                                <select id="test_type" name="test_type">
                                    <option value="basic">Basic Test</option>
                                    <option value="ticket_created">Ticket Created</option>
                                    <option value="invoice_created">Invoice Created</option>
                                </select>
                            </div>

                            <button type="submit" name="test_connection" class="btn btn-info" style="height: 45px;">
                                <i class="fas fa-paper-plane"></i> Send Test Email
                            </button>
                        </div>
                    </div>

                    <div class="settings-section" style="margin-top: 20px;">
                        <h3><i class="fas fa-check-circle"></i> Configuration Status</h3>
                        <table style="width: 100%;">
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">SMTP Status:</td>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                                    <?php if (getSetting('smtp_enabled')): ?>
                                        <span class="status-badge enabled">Enabled</span>
                                    <?php else: ?>
                                        <span class="status-badge disabled">Disabled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">SMTP Host:</td>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color);"><?php echo getSetting('smtp_host') ?: 'Not configured'; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">From Email:</td>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color);"><?php echo getSetting('from_email') ?: 'Not configured'; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-weight: 500;">Active Notifications:</td>
                                <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                                    <?php
                                    $active = 0;
                                    if (getSetting('notify_ticket_created')) $active++;
                                    if (getSetting('notify_ticket_reply_admin')) $active++;
                                    if (getSetting('notify_ticket_reply_client')) $active++;
                                    if (getSetting('notify_invoice_created')) $active++;
                                    if (getSetting('notify_invoice_paid')) $active++;
                                    if (getSetting('notify_invoice_overdue')) $active++;
                                    echo $active . ' enabled';
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" name="save_settings" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save All Settings
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.closest('.tab').classList.add('active');
        }

        function toggleCheckbox(id) {
            const checkbox = document.getElementById(id);
            checkbox.checked = !checkbox.checked;
            updateCheckboxCard(checkbox);
        }

        function updateCheckboxCard(checkbox) {
            const card = checkbox.closest('.checkbox-card');
            if (checkbox.checked) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        }

        // Initialize checkbox cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.checkbox-card input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateCheckboxCard(this);
                });
            });
        });
    </script>
    <script src="../assets/js/panel.js"></script>
</body>
</html>
