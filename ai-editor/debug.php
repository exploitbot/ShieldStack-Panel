<?php
require_once __DIR__ . '/../panel/includes/auth.php';
require_once __DIR__ . '/../panel/includes/database.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();
$customerEmail = $_SESSION['customer_email'] ?? 'not set';

echo "<h1>Debug Information</h1>";
echo "<p>Session Customer ID: " . htmlspecialchars($customerId ?? 'null') . "</p>";
echo "<p>Session Customer Email: " . htmlspecialchars($customerEmail) . "</p>";
echo "<p>Session Data: <pre>" . print_r($_SESSION, true) . "</pre></p>";

// Check AI plan
$stmt = $db->prepare("
    SELECT *
    FROM ai_service_plans
    WHERE customer_id = ? AND status = 'active'
    LIMIT 1
");
$stmt->execute([$customerId]);
$aiPlan = $stmt->fetch();

echo "<h2>AI Plan Query</h2>";
echo "<p>Query: SELECT * FROM ai_service_plans WHERE customer_id = " . htmlspecialchars($customerId) . " AND status = 'active'</p>";
echo "<p>AI Plan Found: " . ($aiPlan ? 'Yes' : 'No') . "</p>";
if ($aiPlan) {
    echo "<pre>" . print_r($aiPlan, true) . "</pre>";
}

// Check SSH credentials
$stmt = $db->prepare("
    SELECT id, customer_id, website_name, is_active
    FROM customer_ssh_credentials
    WHERE customer_id = ? AND is_active = 1
    LIMIT 1
");
$stmt->execute([$customerId]);
$sshCredentials = $stmt->fetch();

echo "<h2>SSH Credentials</h2>";
echo "<p>SSH Credentials Found: " . ($sshCredentials ? 'Yes' : 'No') . "</p>";
if ($sshCredentials) {
    echo "<pre>" . print_r($sshCredentials, true) . "</pre>";
}

// Check customer record
$stmt = $db->prepare("SELECT id, email, full_name FROM customers WHERE email = ?");
$stmt->execute([$customerEmail]);
$customer = $stmt->fetch();

echo "<h2>Customer Record</h2>";
echo "<p>Customer Found: " . ($customer ? 'Yes' : 'No') . "</p>";
if ($customer) {
    echo "<pre>" . print_r($customer, true) . "</pre>";
}
?>