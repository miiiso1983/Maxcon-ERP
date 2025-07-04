<?php

require_once 'Session.php';
require_once 'User.php';
require_once 'Tenant.php';

class Auth {
    
    /**
     * Check if user is authenticated
     */
    public static function check() {
        return User::isLoggedIn();
    }
    
    /**
     * Get current authenticated user
     */
    public static function user() {
        return User::getCurrentUser();
    }
    
    /**
     * Check if current user is super admin
     */
    public static function isSuperAdmin() {
        $user = self::user();
        return $user && $user->isSuperAdmin();
    }
    
    /**
     * Check if current user has access to tenant
     */
    public static function hasAccessToTenant($tenantId) {
        $user = self::user();
        if (!$user) {
            return false;
        }
        
        return $user->hasAccessToTenant($tenantId);
    }
    
    /**
     * Get current user's tenant
     */
    public static function getTenant() {
        $user = self::user();
        if (!$user || !$user->getTenantId()) {
            return null;
        }
        
        $tenant = new Tenant();
        return $tenant->getTenantById($user->getTenantId());
    }
    
    /**
     * Check if current user can access a module
     */
    public static function canAccessModule($module) {
        // Super admin can access everything
        if (self::isSuperAdmin()) {
            return true;
        }
        
        $tenant = self::getTenant();
        if (!$tenant) {
            return false;
        }
        
        return $tenant->hasModuleAccess($module);
    }
    
    /**
     * Require authentication (redirect to login if not authenticated)
     */
    public static function requireAuth($redirectUrl = 'login.php') {
        if (!self::check()) {
            Session::setFlash('error', 'Please log in to access this page.');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Require super admin access
     */
    public static function requireSuperAdmin($redirectUrl = 'dashboard.php') {
        self::requireAuth();
        
        if (!self::isSuperAdmin()) {
            Session::setFlash('error', 'Access denied. Super admin privileges required.');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Require tenant access
     */
    public static function requireTenantAccess($tenantId, $redirectUrl = 'dashboard.php') {
        self::requireAuth();
        
        if (!self::hasAccessToTenant($tenantId)) {
            Session::setFlash('error', 'Access denied. You do not have permission to access this tenant.');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Require module access
     */
    public static function requireModuleAccess($module, $redirectUrl = 'dashboard.php') {
        self::requireAuth();
        
        if (!self::canAccessModule($module)) {
            Session::setFlash('error', 'Access denied. You do not have permission to access this module.');
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Check if current user can perform action on resource
     */
    public static function canPerformAction($action, $resource = null, $resourceId = null) {
        $user = self::user();
        if (!$user) {
            return false;
        }
        
        // Super admin can do everything
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Define role-based permissions
        $permissions = [
            'admin' => [
                'users' => ['create', 'read', 'update', 'delete'],
                'customers' => ['create', 'read', 'update', 'delete'],
                'products' => ['create', 'read', 'update', 'delete'],
                'sales' => ['create', 'read', 'update', 'delete'],
                'reports' => ['read'],
                'settings' => ['read', 'update']
            ],
            'manager' => [
                'customers' => ['create', 'read', 'update'],
                'products' => ['create', 'read', 'update'],
                'sales' => ['create', 'read', 'update'],
                'reports' => ['read']
            ],
            'user' => [
                'customers' => ['read'],
                'products' => ['read'],
                'sales' => ['create', 'read']
            ]
        ];
        
        // Determine user role based on position or department
        $userRole = self::getUserRole($user);
        
        if (!isset($permissions[$userRole])) {
            return false;
        }
        
        if (!isset($permissions[$userRole][$resource])) {
            return false;
        }
        
        return in_array($action, $permissions[$userRole][$resource]);
    }
    
    /**
     * Get user role based on position or department
     */
    private static function getUserRole($user) {
        $position = strtolower($user->getPosition() ?? '');
        $department = strtolower($user->getDepartment() ?? '');
        
        // Check for admin keywords
        if (strpos($position, 'admin') !== false || strpos($department, 'admin') !== false) {
            return 'admin';
        }
        
        // Check for manager keywords
        if (strpos($position, 'manager') !== false || strpos($position, 'supervisor') !== false) {
            return 'manager';
        }
        
        // Default to user role
        return 'user';
    }
    
    /**
     * Get user permissions for a resource
     */
    public static function getUserPermissions($resource) {
        $user = self::user();
        if (!$user) {
            return [];
        }
        
        if ($user->isSuperAdmin()) {
            return ['create', 'read', 'update', 'delete'];
        }
        
        $userRole = self::getUserRole($user);
        
        $permissions = [
            'admin' => [
                'users' => ['create', 'read', 'update', 'delete'],
                'customers' => ['create', 'read', 'update', 'delete'],
                'products' => ['create', 'read', 'update', 'delete'],
                'sales' => ['create', 'read', 'update', 'delete'],
                'reports' => ['read'],
                'settings' => ['read', 'update']
            ],
            'manager' => [
                'customers' => ['create', 'read', 'update'],
                'products' => ['create', 'read', 'update'],
                'sales' => ['create', 'read', 'update'],
                'reports' => ['read']
            ],
            'user' => [
                'customers' => ['read'],
                'products' => ['read'],
                'sales' => ['create', 'read']
            ]
        ];
        
        return $permissions[$userRole][$resource] ?? [];
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        return Session::setCsrfToken();
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        return Session::verifyCsrfToken($token);
    }
    
    /**
     * Logout current user
     */
    public static function logout() {
        User::logout();
    }
}
