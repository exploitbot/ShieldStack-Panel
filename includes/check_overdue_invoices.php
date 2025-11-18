#!/usr/bin/php
<?php
// Cron job to check for overdue invoices and send notifications
// Add to crontab: 0 9 * * * /usr/bin/php /var/www/html/panel/includes/check_overdue_invoices.php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/email.php';

try {
    $db = Database::getInstance()->getConnection();
    $emailService = new EmailService();

    // Find unpaid invoices that are overdue (past due_date)
    $stmt = $db->query("
        SELECT id, invoice_number, customer_id, due_date
        FROM invoices
        WHERE status = 'unpaid'
        AND due_date < CURDATE()
        AND (last_overdue_reminder IS NULL OR last_overdue_reminder < DATE_SUB(NOW(), INTERVAL 7 DAY))
    ");

    $overdueInvoices = $stmt->fetchAll();

    foreach ($overdueInvoices as $invoice) {
        // Send overdue notification
        if ($emailService->sendInvoiceOverdueNotification($invoice['id'])) {
            // Update last reminder date
            $updateStmt = $db->prepare("UPDATE invoices SET last_overdue_reminder = NOW() WHERE id = ?");
            $updateStmt->execute([$invoice['id']]);

            echo "Sent overdue notification for invoice #{$invoice['invoice_number']}\n";
        }
    }

    echo "Checked " . count($overdueInvoices) . " overdue invoices\n";

} catch (Exception $e) {
    error_log("Overdue invoice check failed: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}
