<?php
// Debug version with detailed logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- DEBUG START -->\n";
echo "<!-- Step 1: Starting tickets.php -->\n";

require_once 'includes/auth.php';
echo "<!-- Step 2: Auth.php included -->\n";

require_once 'includes/database.php';
echo "<!-- Step 3: Database.php included -->\n";

$auth = new Auth();
echo "<!-- Step 4: Auth object created -->\n";

echo "<!-- Session data: " . print_r($_SESSION, true) . " -->\n";
echo "<!-- Is logged in: " . ($auth->isLoggedIn() ? 'YES' : 'NO') . " -->\n";
echo "<!-- Customer ID: " . ($auth->getCurrentCustomerId() ?? 'NULL') . " -->\n";

$auth->requireLogin();
echo "<!-- Step 5: requireLogin() passed -->\n";

$db = Database::getInstance()->getConnection();
$customerId = $auth->getCurrentCustomerId();

echo "SUCCESS! You should see the tickets page below.\n";
echo "Customer ID: $customerId\n";

// Rest of the page would go here
?>
