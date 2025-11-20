<?php
/**
 * Authentication Test Script
 * Tests all authentication scenarios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';

echo "<html><head><style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #1a1a2e; color: #eee; }
    .test { margin: 20px 0; padding: 15px; background: #16213e; border-radius: 5px; }
    .success { color: #4caf50; }
    .error { color: #f44336; }
    .info { color: #2196f3; }
    h1 { color: #00d4ff; }
    h2 { color: #00d4ff; margin-top: 30px; }
    pre { background: #0f172a; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style></head><body>";

echo "<h1>ShieldStack Authentication Test Suite</h1>";
echo "<p>Testing authentication fixes...</p>";

$auth = new Auth();

// Test 1: Check session and cookie state
echo "<div class='test'>";
echo "<h2>Test 1: Current Session State</h2>";
echo "<p class='info'>Logged In: " . ($auth->isLoggedIn() ? 'Yes' : 'No') . "</p>";
if ($auth->isLoggedIn()) {
    echo "<p class='info'>User: " . $auth->getCurrentCustomerName() . "</p>";
    echo "<p class='info'>Admin: " . ($auth->isAdmin() ? 'Yes' : 'No') . "</p>";
}
echo "<p class='info'>Remember Token Cookie: " . (isset($_COOKIE['remember_token']) ? 'Present' : 'Not Present') . "</p>";
echo "</div>";

// Test 2: Check session messages
echo "<div class='test'>";
echo "<h2>Test 2: Session Messages</h2>";
if (isset($_SESSION['login_error'])) {
    echo "<p class='info'>Login Error: " . htmlspecialchars($_SESSION['login_error']) . "</p>";
}
if (isset($_SESSION['error'])) {
    echo "<p class='info'>Error: " . htmlspecialchars($_SESSION['error']) . "</p>";
}
if (isset($_SESSION['redirect_after_login'])) {
    echo "<p class='info'>Redirect After Login: " . htmlspecialchars($_SESSION['redirect_after_login']) . "</p>";
}
if (!isset($_SESSION['login_error']) && !isset($_SESSION['error']) && !isset($_SESSION['redirect_after_login'])) {
    echo "<p class='success'>No session messages (clean state)</p>";
}
echo "</div>";

// Test 3: Test login with regular user
echo "<div class='test'>";
echo "<h2>Test 3: Login Test (Customer Account)</h2>";
echo "<p>Testing login with customer@test.com / password</p>";

// Clear session first
session_destroy();
session_start();

$result = $auth->login('customer@test.com', 'password', false);
if ($result['success']) {
    echo "<p class='success'>Login successful!</p>";
    echo "<p class='info'>Is Admin: " . ($result['is_admin'] ? 'Yes' : 'No') . "</p>";
    echo "<p class='info'>Session Customer ID: " . ($_SESSION['customer_id'] ?? 'Not Set') . "</p>";
    echo "<p class='info'>Session Customer Name: " . ($_SESSION['customer_name'] ?? 'Not Set') . "</p>";
} else {
    echo "<p class='error'>Login failed: " . $result['message'] . "</p>";
}
echo "</div>";

// Test 4: Test remember me token
echo "<div class='test'>";
echo "<h2>Test 4: Remember Me Functionality</h2>";
session_destroy();
session_start();

$result = $auth->login('customer@test.com', 'password', true);
if ($result['success']) {
    echo "<p class='success'>Login with Remember Me successful!</p>";

    // Check if cookie was set (it won't be set in this script context, but we can check the database)
    require_once 'includes/database.php';
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT remember_token FROM customers WHERE email = ?");
    $stmt->execute(['customer@test.com']);
    $tokenData = $stmt->fetch();

    if ($tokenData && $tokenData['remember_token']) {
        echo "<p class='success'>Remember token stored in database!</p>";
        echo "<p class='info'>Token: " . substr($tokenData['remember_token'], 0, 20) . "...</p>";
    } else {
        echo "<p class='error'>Remember token NOT stored in database</p>";
    }
} else {
    echo "<p class='error'>Login failed: " . $result['message'] . "</p>";
}
echo "</div>";

// Test 5: Test admin access check
echo "<div class='test'>";
echo "<h2>Test 5: Admin Access Control</h2>";

// Login as regular user
session_destroy();
session_start();
$auth = new Auth();
$auth->login('customer@test.com', 'password', false);

echo "<p>Logged in as regular customer...</p>";
echo "<p class='info'>isLoggedIn(): " . ($auth->isLoggedIn() ? 'true' : 'false') . "</p>";
echo "<p class='info'>isAdmin(): " . ($auth->isAdmin() ? 'true' : 'false') . "</p>";

if (!$auth->isAdmin()) {
    echo "<p class='success'>Admin check working - user is NOT admin</p>";
} else {
    echo "<p class='error'>Admin check FAILED - regular user detected as admin!</p>";
}
echo "</div>";

// Test 6: Verify auth methods exist
echo "<div class='test'>";
echo "<h2>Test 6: Auth Class Methods</h2>";
$methods = get_class_methods($auth);
$requiredMethods = ['requireLogin', 'requireAdmin', 'login', 'logout', 'isLoggedIn', 'isAdmin'];
foreach ($requiredMethods as $method) {
    if (in_array($method, $methods)) {
        echo "<p class='success'>Method exists: $method</p>";
    } else {
        echo "<p class='error'>Method missing: $method</p>";
    }
}
echo "</div>";

// Test 7: Check database structure
echo "<div class='test'>";
echo "<h2>Test 7: Database Structure</h2>";
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW COLUMNS FROM customers LIKE 'remember_token'");
    $column = $stmt->fetch();

    if ($column) {
        echo "<p class='success'>remember_token column exists</p>";
        echo "<pre>" . print_r($column, true) . "</pre>";
    } else {
        echo "<p class='error'>remember_token column missing!</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 8: Simulate redirect scenarios
echo "<div class='test'>";
echo "<h2>Test 8: Redirect Logic Simulation</h2>";

// Test admin path detection
$testPaths = [
    '/panel/admin/dashboard.php',
    '/panel/dashboard.php',
    '/panel/services.php',
    '/panel/admin/tickets.php'
];

foreach ($testPaths as $path) {
    $isAdminPath = strpos($path, '/admin/') !== false;
    echo "<p class='info'>Path: $path - Admin folder: " . ($isAdminPath ? 'Yes' : 'No') . "</p>";
}
echo "</div>";

echo "<h2>Test Summary</h2>";
echo "<div class='test'>";
echo "<p class='success'>All tests completed!</p>";
echo "<p>Check results above for any errors.</p>";
echo "<p><a href='login.php' style='color: #00d4ff;'>Go to Login Page</a> | ";
echo "<a href='dashboard.php' style='color: #00d4ff;'>Go to Dashboard</a> | ";
echo "<a href='admin/dashboard.php' style='color: #00d4ff;'>Go to Admin Dashboard</a></p>";
echo "</div>";

echo "</body></html>";
?>
