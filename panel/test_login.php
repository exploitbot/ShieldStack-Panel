<?php
// Test script to diagnose blank screen issue
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Login Test</title></head><body>";
echo "<h1>ShieldStack Login Test</h1>";

// Test 1: PHP is working
echo "<p>✓ PHP is working</p>";

// Test 2: Check includes
echo "<p>Testing includes...</p>";
try {
    require_once "includes/auth.php";
    echo "<p>✓ auth.php loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ auth.php error: " . $e->getMessage() . "</p>";
}

// Test 3: Check database
try {
    require_once "includes/database.php";
    $db = Database::getInstance()->getConnection();
    echo "<p>✓ Database connected</p>";
} catch (Exception $e) {
    echo "<p>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test 4: Check CSS file
$cssPath = __DIR__ . "/assets/css/style.css";
if (file_exists($cssPath)) {
    echo "<p>✓ CSS file exists</p>";
} else {
    echo "<p>✗ CSS file not found at: $cssPath</p>";
}

// Test 5: Check session
session_start();
echo "<p>✓ Session started</p>";

// Test 6: Try creating Auth object
try {
    $auth = new Auth();
    echo "<p>✓ Auth object created</p>";
    echo "<p>Logged in: " . ($auth->isLoggedIn() ? "Yes" : "No") . "</p>";
} catch (Exception $e) {
    echo "<p>✗ Auth error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href=\"login.php\">Go to Login Page</a></p>";
echo "</body></html>";
?>
