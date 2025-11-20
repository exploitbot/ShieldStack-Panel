<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $db;
    private $settings;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->loadSettings();
    }

    private function loadSettings() {
        $stmt = $this->db->query("SELECT `key`, `value` FROM system_settings");
        $this->settings = [];
        while ($row = $stmt->fetch()) {
            $this->settings[$row['key']] = $row['value'];
        }
    }

    private function getSetting($key, $default = '') {
        return $this->settings[$key] ?? $default;
    }

    private function isEnabled() {
        return (bool)$this->getSetting('smtp_enabled', 0);
    }

    private function createMailer() {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $this->getSetting('smtp_host');
        $mail->SMTPAuth = true;
        $mail->Username = $this->getSetting('smtp_username');
        $mail->Password = $this->getSetting('smtp_password');
        $mail->SMTPSecure = $this->getSetting('smtp_encryption', 'tls');
        $mail->Port = (int)$this->getSetting('smtp_port', 587);

        $mail->setFrom(
            $this->getSetting('from_email'),
            $this->getSetting('from_name', 'ShieldStack')
        );

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        return $mail;
    }

    private function getEmailTemplate($title, $content, $actionUrl = null, $actionText = null) {
        $actionButton = '';
        if ($actionUrl && $actionText) {
            $actionButton = "
            <table role=\"presentation\" style=\"margin: 30px auto;\">
                <tr>
                    <td style=\"border-radius: 5px; background: $headerColor;\">
                        <a href=\"{$actionUrl}\" style=\"border: none; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; font-weight: bold; border-radius: 5px;\">{$actionText}</a>
                    </td>
                </tr>
            </table>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset=\"UTF-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <title>{$title}</title>
        </head>
        <body style=\"margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;\">
            <table role=\"presentation\" style=\"width: 100%; border-collapse: collapse;\">
                <tr>
                    <td align=\"center\" style=\"padding: 40px 0;\">
                        <table role=\"presentation\" style=\"width: 600px; border-collapse: collapse; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);\">
                            <!-- Header -->
                            <tr>
                                <td style=\"padding: 40px; background: $headerColor; border-radius: 10px 10px 0 0; text-align: center;\">
                                    <h1 style=\"margin: 0; color: white; font-size: 28px;\">{$title}</h1>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style=\"padding: 40px; color: #333; font-size: 16px; line-height: 1.6;\">
                                    {$content}
                                    {$actionButton}
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style=\"padding: 30px; background: #f9f9f9; border-radius: 0 0 10px 10px; text-align: center; color: #666; font-size: 14px;\">
                                    <p style=\"margin: 0 0 10px 0;\">This is an automated message from ShieldStack</p>
                                    <p style=\"margin: 0; color: #999;\">© " . date('Y') . " ShieldStack. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }

    // Ticket Created Notification
    public function sendTicketCreatedNotification($ticketId) {
        if (!$this->isEnabled() || !$this->getSetting('notify_ticket_created')) {
            return false;
        }

        try {
            // Get ticket details
            $stmt = $this->db->prepare("
                SELECT t.*, c.full_name, c.email, c.company
                FROM tickets t
                JOIN customers c ON t.customer_id = c.id
                WHERE t.id = ?
            ");
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch();

            if (!$ticket) return false;

            // Get admin emails
            $adminStmt = $this->db->query("SELECT email FROM customers WHERE role = 'admin' AND status = 'active'");
            $admins = $adminStmt->fetchAll();

            $mail = $this->createMailer();
            $mail->Subject = "New Support Ticket #{$ticketId}: {$ticket['subject']}";

            $content = "
                <p>A new support ticket has been created and requires your attention.</p>

                <table style=\"width: 100%; margin: 20px 0; border-collapse: collapse;\">
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;\">Ticket ID:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">#{$ticketId}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Subject:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">{$ticket['subject']}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Customer:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">{$ticket['full_name']} ({$ticket['email']})</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Department:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">{$ticket['department']}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Priority:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\"><span style=\"text-transform: uppercase; font-weight: bold;\">{$ticket['priority']}</span></td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Status:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">{$ticket['status']}</td>
                    </tr>
                </table>

                <p><strong>Message:</strong></p>
                <div style=\"background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 15px 0;\">
                    " . nl2br(htmlspecialchars($ticket['message'])) . "
                </div>
            ";

            $actionUrl = "https://" . $_SERVER['HTTP_HOST'] . "/panel/admin/ticket-view.php?id={$ticketId}";
            $mail->Body = $this->getEmailTemplate(
                "New Support Ticket #{$ticketId}",
                $content,
                $actionUrl,
                "View Ticket"
            );

            // Send to all admins
            foreach ($admins as $admin) {
                $mail->addAddress($admin['email']);
            }

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Email notification failed: " . $e->getMessage());
            return false;
        }
    }

    // Ticket Reply Notification
    public function sendTicketReplyNotification($ticketId, $replyBy) {
        if (!$this->isEnabled() || !$this->getSetting('notify_ticket_reply')) {
            return false;
        }

        try {
            // Get ticket and latest reply details
            $stmt = $this->db->prepare("
                SELECT t.*, c.full_name, c.email,
                       r.message as reply_message, r.created_at as reply_date
                FROM tickets t
                JOIN customers c ON t.customer_id = c.id
                LEFT JOIN ticket_replies r ON t.id = r.ticket_id
                WHERE t.id = ?
                ORDER BY r.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch();

            if (!$ticket) return false;

            $mail = $this->createMailer();
            $mail->Subject = "New Reply on Ticket #{$ticketId}: {$ticket['subject']}";

            $replyByText = ($replyBy === 'admin') ? 'Support Team' : $ticket['full_name'];

            $content = "
                <p>A new reply has been added to support ticket #{$ticketId}.</p>

                <table style=\"width: 100%; margin: 20px 0; border-collapse: collapse;\">
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;\">Ticket:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">#{$ticketId} - {$ticket['subject']}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Reply By:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">{$replyByText}</td>
                    </tr>
                </table>

                <p><strong>Latest Reply:</strong></p>
                <div style=\"background: #f0f7ff; padding: 20px; border-left: 4px solid #667eea; border-radius: 5px; margin: 15px 0;\">
                    " . nl2br(htmlspecialchars($ticket['reply_message'])) . "
                </div>
            ";

            $actionUrl = "https://" . $_SERVER['HTTP_HOST'] . "/panel/admin/ticket-view.php?id={$ticketId}";
            $mail->Body = $this->getEmailTemplate(
                "New Reply on Ticket #{$ticketId}",
                $content,
                $actionUrl,
                "View Ticket"
            );

            // Send to customer if admin replied, or to admins if customer replied
            if ($replyBy === 'admin') {
                $mail->addAddress($ticket['email']);
            } else {
                $adminStmt = $this->db->query("SELECT email FROM customers WHERE role = 'admin' AND status = 'active'");
                $admins = $adminStmt->fetchAll();
                foreach ($admins as $admin) {
                    $mail->addAddress($admin['email']);
                }
            }

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Email notification failed: " . $e->getMessage());
            return false;
        }
    }

    // Invoice Created Notification
    public function sendInvoiceCreatedNotification($invoiceId) {
        if (!$this->isEnabled() || !$this->getSetting('notify_invoice_created')) {
            return false;
        }

        try {
            // Get invoice details
            $stmt = $this->db->prepare("
                SELECT i.*, c.full_name, c.email, c.company
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                WHERE i.id = ?
            ");
            $stmt->execute([$invoiceId]);
            $invoice = $stmt->fetch();

            if (!$invoice) return false;

            $mail = $this->createMailer();
            $mail->addAddress($invoice['email']);
            $mail->Subject = "New Invoice #{$invoice['invoice_number']} from ShieldStack";

            $content = "
                <p>Hello {$invoice['full_name']},</p>
                <p>A new invoice has been generated for your account.</p>

                <table style=\"width: 100%; margin: 20px 0; border-collapse: collapse;\">
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;\">Invoice Number:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">#{$invoice['invoice_number']}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Amount:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-size: 20px; color: #667eea;\"><strong>\${$invoice['amount']}</strong></td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Due Date:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">" . date('F j, Y', strtotime($invoice['due_date'])) . "</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Status:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\"><span style=\"text-transform: uppercase;\">{$invoice['status']}</span></td>
                    </tr>
                </table>

                <p>Please login to your client portal to view and pay this invoice.</p>
            ";

            $actionUrl = "https://" . $_SERVER['HTTP_HOST'] . "/panel/invoices.php";
            $mail->Body = $this->getEmailTemplate(
                "New Invoice #{$invoice['invoice_number']}",
                $content,
                $actionUrl,
                "View & Pay Invoice"
            );

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Email notification failed: " . $e->getMessage());
            return false;
        }
    }

    // Invoice Paid Notification
    public function sendInvoicePaidNotification($invoiceId) {
        if (!$this->isEnabled() || !$this->getSetting('notify_invoice_paid')) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT i.*, c.full_name, c.email, c.company
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                WHERE i.id = ?
            ");
            $stmt->execute([$invoiceId]);
            $invoice = $stmt->fetch();

            if (!$invoice) return false;

            $mail = $this->createMailer();

            // Notify customer
            $mail->addAddress($invoice['email']);
            $mail->Subject = "Payment Received - Invoice #{$invoice['invoice_number']}";

            $content = "
                <p>Hello {$invoice['full_name']},</p>
                <p>Thank you! We have received your payment for invoice #{$invoice['invoice_number']}.</p>

                <table style=\"width: 100%; margin: 20px 0; border-collapse: collapse;\">
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;\">Invoice Number:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">#{$invoice['invoice_number']}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Amount Paid:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-size: 20px; color: #43e97b;\"><strong>\${$invoice['amount']}</strong></td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Payment Date:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">" . date('F j, Y') . "</td>
                    </tr>
                </table>

                <p>Your services will continue uninterrupted. Thank you for your business!</p>
            ";

            $actionUrl = "https://" . $_SERVER['HTTP_HOST'] . "/panel/invoices.php";
            $mail->Body = $this->getEmailTemplate(
                "Payment Received",
                $content,
                $actionUrl,
                "View Invoices"
            );

            $mail->send();

            // Also notify admins
            $mail->clearAddresses();
            $adminStmt = $this->db->query("SELECT email FROM customers WHERE role = 'admin' AND status = 'active'");
            $admins = $adminStmt->fetchAll();
            foreach ($admins as $admin) {
                $mail->addAddress($admin['email']);
            }

            $mail->Subject = "Payment Received - Invoice #{$invoice['invoice_number']} from {$invoice['full_name']}";
            $mail->send();

            return true;

        } catch (Exception $e) {
            error_log("Email notification failed: " . $e->getMessage());
            return false;
        }
    }

    // Invoice Overdue Notification
    public function sendInvoiceOverdueNotification($invoiceId) {
        if (!$this->isEnabled() || !$this->getSetting('notify_invoice_overdue')) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT i.*, c.full_name, c.email, c.company
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                WHERE i.id = ?
            ");
            $stmt->execute([$invoiceId]);
            $invoice = $stmt->fetch();

            if (!$invoice) return false;

            $daysOverdue = floor((time() - strtotime($invoice['due_date'])) / 86400);

            $mail = $this->createMailer();
            $mail->addAddress($invoice['email']);
            $mail->Subject = "Overdue Invoice Reminder - #{$invoice['invoice_number']}";

            $content = "
                <p>Hello {$invoice['full_name']},</p>
                <p style=\"color: #f5576c;\"><strong>This is a friendly reminder that invoice #{$invoice['invoice_number']} is now overdue.</strong></p>

                <table style=\"width: 100%; margin: 20px 0; border-collapse: collapse;\">
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;\">Invoice Number:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">#{$invoice['invoice_number']}</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Amount Due:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-size: 20px; color: #f5576c;\"><strong>\${$invoice['amount']}</strong></td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Due Date:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee;\">" . date('F j, Y', strtotime($invoice['due_date'])) . "</td>
                    </tr>
                    <tr>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;\">Days Overdue:</td>
                        <td style=\"padding: 10px; border-bottom: 1px solid #eee; color: #f5576c;\"><strong>{$daysOverdue} days</strong></td>
                    </tr>
                </table>

                <p>Please submit your payment as soon as possible to avoid any service interruptions.</p>
                <p>If you have already paid, please disregard this notice. If you have any questions, please contact our support team.</p>
            ";

            $actionUrl = "https://" . $_SERVER['HTTP_HOST'] . "/panel/invoices.php";
            $mail->Body = $this->getEmailTemplate(
                "Overdue Invoice Reminder",
                $content,
                $actionUrl,
                "Pay Now"
            );

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Email notification failed: " . $e->getMessage());
            return false;
        }
    }

    // Test email function
    public function sendTestEmail($toEmail) {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($toEmail);
            $mail->Subject = "Test Email from ShieldStack";

            $content = "
                <p>Congratulations! Your SMTP configuration is working correctly.</p>
                <p>This is a test email sent from your ShieldStack admin panel.</p>

                <div style=\"background: #f0f7ff; padding: 20px; border-left: 4px solid #667eea; border-radius: 5px; margin: 20px 0;\">
                    <p style=\"margin: 0;\"><strong>SMTP Configuration Details:</strong></p>
                    <ul style=\"margin: 10px 0 0 0; padding-left: 20px;\">
                        <li>Host: " . $this->getSetting('smtp_host') . "</li>
                        <li>Port: " . $this->getSetting('smtp_port') . "</li>
                        <li>Encryption: " . strtoupper($this->getSetting('smtp_encryption')) . "</li>
                        <li>From: " . $this->getSetting('from_email') . "</li>
                    </ul>
                </div>

                <p>You can now enable email notifications for tickets and invoices in your admin panel.</p>
            ";

            $mail->Body = $this->getEmailTemplate(
                "Test Email Successful!",
                $content
            );

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Test email failed: " . $e->getMessage());
            throw $e;
        }
    }
}
