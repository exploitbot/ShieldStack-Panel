<?php
/**
 * CSRF Protection Helper
 * Generates and validates CSRF tokens for form submissions
 */

class CSRFProtection {
    private static $tokenName = 'csrf_token';
    private static $tokenExpiry = 7200; // 2 hours

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
     * Get the current CSRF token (generate if not exists)
     * @return string The current token
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generate new token if none exists or expired
        if (!isset($_SESSION[self::$tokenName]) || self::isTokenExpired()) {
            return self::generateToken();
        }

        return $_SESSION[self::$tokenName];
    }

    /**
     * Check if token is expired
     * @return bool True if expired
     */
    private static function isTokenExpired() {
        $tokenTime = $_SESSION[self::$tokenName . '_time'] ?? 0;
        return (time() - $tokenTime) > self::$tokenExpiry;
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

        // No token provided
        if (empty($token)) {
            return false;
        }

        // Check if session token exists
        if (!isset($_SESSION[self::$tokenName])) {
            return false;
        }

        // Check if token is expired
        if (self::isTokenExpired()) {
            self::destroyToken();
            return false;
        }

        // Validate token using timing-safe comparison
        $valid = hash_equals($_SESSION[self::$tokenName], $token);

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

    /**
     * Get token name for use in AJAX requests
     * @return string The token field name
     */
    public static function getTokenName() {
        return self::$tokenName;
    }
}
?>
