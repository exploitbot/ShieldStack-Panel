<?php
require_once 'includes/auth.php';

$auth = new Auth();

// Get error messages from session
$error = $_SESSION['login_error'] ?? $_SESSION['error'] ?? '';
$redirectTo = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
$success = '';

// Clear session messages
unset($_SESSION['login_error'], $_SESSION['error'], $_SESSION['redirect_after_login']);

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';

    // Store redirect for after successful login
    $redirectAfterLogin = $_POST['redirect_to'] ?? 'dashboard.php';

    $result = $auth->login($email, $password, $rememberMe);

    if ($result['success']) {
        if ($result['is_admin']) {
            header('Location: admin/dashboard.php');
        } else {
            // Redirect to original requested page or dashboard
            header('Location: ' . $redirectAfterLogin);
        }
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShieldStack Client Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <svg width="60" height="60" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 2L4 10V18C4 27.5 10.5 36.1 20 38C29.5 36.1 36 27.5 36 18V10L20 2Z" stroke="#00d4ff" stroke-width="2" fill="rgba(0,212,255,0.1)"/>
                    <path d="M20 12V28M14 20H26" stroke="#00d4ff" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <h1>ShieldStack</h1>
                <p>Client Portal Login</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirectTo); ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span>Remember me for 30 days</span>
                    </label>
                </div>

                <button type="submit" name="login" class="btn btn-primary">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
                <p><a href="/">Back to main site</a></p>
            </div>
        </div>
    </div>
</body>
</html>
