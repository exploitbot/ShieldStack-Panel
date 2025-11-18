<?php
// Authentication and session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

class Auth {
    private $db;

    public function __construct($dbConnection = null) {
        if ($dbConnection !== null) {
            $this->db = $dbConnection;
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
        $this->checkRememberMe();
    }

    private function checkRememberMe() {
        if (!$this->isLoggedIn() && isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $stmt = $this->db->prepare("SELECT * FROM customers WHERE remember_token = ? AND status = 'active'");
            $stmt->execute([$token]);
            $customer = $stmt->fetch();

            if ($customer) {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_email'] = $customer['email'];
                $_SESSION['customer_name'] = $customer['full_name'];
                $_SESSION['is_admin'] = $customer['is_admin'];
                $_SESSION['logged_in'] = true;
            }
        }
    }

    public function register($email, $password, $fullName, $company = null, $phone = null) {
        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email address'];
            }

            if (strlen($password) < 8) {
                return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
            }

            $stmt = $this->db->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->db->prepare("
                INSERT INTO customers (email, password, full_name, company, phone)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$email, $hashedPassword, $fullName, $company, $phone]);

            return ['success' => true, 'message' => 'Registration successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    public function login($email, $password, $rememberMe = false) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM customers WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $customer = $stmt->fetch();

            if (!$customer) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            if (!password_verify($password, $customer['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_name'] = $customer['full_name'];
            $_SESSION['is_admin'] = $customer['is_admin'];
            $_SESSION['logged_in'] = true;

            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                $stmt = $this->db->prepare("UPDATE customers SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $customer['id']]);

                // Set cookie for 30 days
                $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                setcookie('remember_token', $token, [
                    'expires' => time() + (30 * 24 * 60 * 60),
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            return ['success' => true, 'message' => 'Login successful', 'is_admin' => $customer['is_admin']];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    public function logout() {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $stmt = $this->db->prepare("UPDATE customers SET remember_token = NULL WHERE remember_token = ?");
            $stmt->execute([$token]);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['login_error'] = 'Please login to continue';
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

            // Check if we're in ai-editor folder
            if (strpos($_SERVER['SCRIPT_NAME'], '/ai-editor/') !== false) {
                if (strpos($_SERVER['SCRIPT_NAME'], '/ai-editor/admin/') !== false) {
                    header('Location: /panel/admin/login.php');
                } else {
                    header('Location: /panel/login.php');
                }
            }
            // Check if we're in admin folder
            else if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) {
                header('Location: ../login.php');
            } else {
                header('Location: login.php');
            }
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Access denied. Admin privileges required.';

            // Redirect to customer dashboard
            if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) {
                header('Location: ../dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }
    }

    public function getCurrentCustomerId() {
        return $_SESSION['customer_id'] ?? null;
    }

    public function getCurrentCustomerName() {
        return $_SESSION['customer_name'] ?? 'Guest';
    }
}
?>
