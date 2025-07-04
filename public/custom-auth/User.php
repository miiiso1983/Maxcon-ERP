<?php

require_once 'Database.php';

class User {
    private $db;
    private $id;
    private $name;
    private $email;
    private $phone;
    private $address;
    private $department;
    private $position;
    private $is_super_admin;
    private $tenant_id;
    private $status;
    private $created_at;
    private $updated_at;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authenticate user with email and password
     */
    public function login($email, $password) {
        try {
            // Find user by email
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE email = ? AND status = 'active'",
                [$email]
            );
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Verify password (Laravel uses bcrypt)
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            // Load user data
            $this->loadUserData($user);
            
            // Start session
            $this->startSession();
            
            // Update last login time
            $this->updateLastLogin();
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $this->getUserData()];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Load user data from database record
     */
    private function loadUserData($userData) {
        $this->id = $userData['id'];
        $this->name = $userData['name'];
        $this->email = $userData['email'];
        $this->phone = $userData['phone'] ?? '';
        $this->address = $userData['address'] ?? '';
        $this->department = $userData['department'] ?? '';
        $this->position = $userData['position'] ?? '';
        $this->is_super_admin = (bool)$userData['is_super_admin'];
        $this->tenant_id = $userData['tenant_id'];
        $this->status = $userData['status'];
        $this->created_at = $userData['created_at'];
        $this->updated_at = $userData['updated_at'];
    }
    
    /**
     * Start secure session
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            
            session_start();
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Store user data in session
        $_SESSION['user_id'] = $this->id;
        $_SESSION['user_email'] = $this->email;
        $_SESSION['user_name'] = $this->name;
        $_SESSION['is_super_admin'] = $this->is_super_admin;
        $_SESSION['tenant_id'] = $this->tenant_id;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin() {
        $this->db->query(
            "UPDATE users SET updated_at = NOW() WHERE id = ?",
            [$this->id]
        );
        
        // Update tenant last login if applicable
        if ($this->tenant_id) {
            $this->db->query(
                "UPDATE tenants SET last_login_at = NOW() WHERE id = ?",
                [$this->tenant_id]
            );
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
            return false;
        }
        
        // Check session timeout (2 hours)
        $sessionTimeout = 2 * 60 * 60; // 2 hours in seconds
        if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
            self::logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Get current logged in user data
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        $user = new self();
        $userData = $user->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        
        if ($userData) {
            $user->loadUserData($userData);
            return $user;
        }
        
        return null;
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
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
     * Get user data as array
     */
    public function getUserData() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'department' => $this->department,
            'position' => $this->position,
            'is_super_admin' => $this->is_super_admin,
            'tenant_id' => $this->tenant_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    // Getter methods
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPhone() { return $this->phone; }
    public function getDepartment() { return $this->department; }
    public function getPosition() { return $this->position; }
    public function isSuperAdmin() { return $this->is_super_admin; }
    public function getTenantId() { return $this->tenant_id; }
    public function getStatus() { return $this->status; }
    
    /**
     * Check if user has access to tenant
     */
    public function hasAccessToTenant($tenantId) {
        if ($this->is_super_admin) {
            return true; // Super admin has access to all tenants
        }
        
        return $this->tenant_id === $tenantId;
    }
}
