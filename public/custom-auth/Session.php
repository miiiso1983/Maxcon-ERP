<?php

class Session {
    
    /**
     * Initialize secure session
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Set session timeout
            ini_set('session.gc_maxlifetime', 7200); // 2 hours
            
            session_start();
        }
    }
    
    /**
     * Set session variable
     */
    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session variable
     */
    public static function get($key, $default = null) {
        self::init();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session variable exists
     */
    public static function has($key) {
        self::init();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session variable
     */
    public static function remove($key) {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Regenerate session ID for security
     */
    public static function regenerate() {
        self::init();
        session_regenerate_id(true);
    }
    
    /**
     * Destroy session completely
     */
    public static function destroy() {
        self::init();
        
        // Clear all session data
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Set flash message
     */
    public static function setFlash($type, $message) {
        self::init();
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get and remove flash message
     */
    public static function getFlash($type) {
        self::init();
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
    
    /**
     * Check if there are any flash messages
     */
    public static function hasFlash($type = null) {
        self::init();
        if ($type) {
            return isset($_SESSION['flash'][$type]);
        }
        return !empty($_SESSION['flash']);
    }
    
    /**
     * Get all flash messages and clear them
     */
    public static function getAllFlash() {
        self::init();
        $flash = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $flash;
    }
    
    /**
     * Check session timeout
     */
    public static function checkTimeout($timeoutSeconds = 7200) {
        self::init();
        
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        if (time() - $_SESSION['last_activity'] > $timeoutSeconds) {
            self::destroy();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Get session ID
     */
    public static function getId() {
        self::init();
        return session_id();
    }
    
    /**
     * Get all session data
     */
    public static function all() {
        self::init();
        return $_SESSION;
    }
    
    /**
     * Check if session is active
     */
    public static function isActive() {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    
    /**
     * Set CSRF token
     */
    public static function setCsrfToken() {
        self::init();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get CSRF token
     */
    public static function getCsrfToken() {
        self::init();
        return $_SESSION['csrf_token'] ?? null;
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        self::init();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
