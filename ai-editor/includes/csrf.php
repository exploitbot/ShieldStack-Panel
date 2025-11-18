<?php
/**
 * CSRF Protection Helper
 * Generates and validates CSRF tokens for form submissions
 */

class CSRFProtection {
    private static $tokenName = 'csrf_token';
    private static $tokenExpiry = 3600; // 1 hour

    /**
     * Generate a CSRF token and store in session
     * @return string The generated token
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::$tokenName] = $token;
        $_SESSION[self::$tokenName . '_time'] = time();

        return $token;
    }

    /**
     * Get the current CSRF token
     * @return string|null The current token or null if not set
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::$tokenName] ?? null;
    }

    /**
     * Validate CSRF token from POST request
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get token from POST if not provided
        if ($token === null) {
            $token = $_POST[self::$tokenName] ?? $_GET[self::$tokenName] ?? null;
        }

        // Check if session token exists
        if (!isset($_SESSION[self::$tokenName])) {
            return false;
        }

        // Check if token is expired
        $tokenTime = $_SESSION[self::$tokenName . '_time'] ?? 0;
        if (time() - $tokenTime > self::$tokenExpiry) {
            self::destroyToken();
            return false;
        }

        // Validate token
        $valid = hash_equals($_SESSION[self::$tokenName], $token);

        // Regenerate token after validation (one-time use)
        if ($valid) {
            self::destroyToken();
        }

        return $valid;
    }

    /**
     * Destroy the current CSRF token
     */
    public static function destroyToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION[self::$tokenName]);
        unset($_SESSION[self::$tokenName . '_time']);
    }

    /**
     * Output hidden input field with CSRF token
     */
    public static function tokenField() {
        $token = self::getToken();
        if (!$token) {
            $token = self::generateToken();
        }

        echo '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Require valid CSRF token or die
     */
    public static function requireToken() {
        if (!self::validateToken()) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh the page and try again.');
        }
    }
}
?>
