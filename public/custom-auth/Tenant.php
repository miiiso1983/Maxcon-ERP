<?php

require_once 'Database.php';

class Tenant {
    private $db;
    private $id;
    private $name;
    private $email;
    private $phone;
    private $address;
    private $license_key;
    private $license_type;
    private $license_expires_at;
    private $status;
    private $max_users;
    private $current_users;
    private $max_warehouses;
    private $current_warehouses;
    private $enabled_modules;
    private $admin_user_id;
    private $admin_name;
    private $admin_email;
    private $last_login_at;
    private $billing_status;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get tenant by ID
     */
    public function getTenantById($tenantId) {
        try {
            $tenant = $this->db->fetch(
                "SELECT * FROM tenants WHERE id = ? AND status = 'active'",
                [$tenantId]
            );
            
            if ($tenant) {
                $this->loadTenantData($tenant);
                return $this;
            }
            
            return null;
        } catch (Exception $e) {
            throw new Exception('Failed to get tenant: ' . $e->getMessage());
        }
    }
    
    /**
     * Load tenant data from database record
     */
    private function loadTenantData($tenantData) {
        $this->id = $tenantData['id'];
        $this->name = $tenantData['name'];
        $this->email = $tenantData['email'];
        $this->phone = $tenantData['phone'];
        $this->address = $tenantData['address'];
        $this->license_key = $tenantData['license_key'];
        $this->license_type = $tenantData['license_type'];
        $this->license_expires_at = $tenantData['license_expires_at'];
        $this->status = $tenantData['status'];
        $this->max_users = (int)$tenantData['max_users'];
        $this->current_users = (int)$tenantData['current_users'];
        $this->max_warehouses = (int)$tenantData['max_warehouses'];
        $this->current_warehouses = (int)$tenantData['current_warehouses'];
        $this->enabled_modules = json_decode($tenantData['enabled_modules'] ?? '[]', true);
        $this->admin_user_id = $tenantData['admin_user_id'];
        $this->admin_name = $tenantData['admin_name'];
        $this->admin_email = $tenantData['admin_email'];
        $this->last_login_at = $tenantData['last_login_at'];
        $this->billing_status = $tenantData['billing_status'];
    }
    
    /**
     * Check if tenant has access to a specific module
     */
    public function hasModuleAccess($module) {
        if (empty($this->enabled_modules)) {
            return true; // If no restrictions, allow all modules
        }
        
        return in_array($module, $this->enabled_modules);
    }
    
    /**
     * Check if tenant can add more users
     */
    public function canAddUsers() {
        return $this->current_users < $this->max_users;
    }
    
    /**
     * Check if tenant can add more warehouses
     */
    public function canAddWarehouses() {
        return $this->current_warehouses < $this->max_warehouses;
    }
    
    /**
     * Check if license is valid
     */
    public function isLicenseValid() {
        if ($this->status !== 'active') {
            return false;
        }
        
        if ($this->billing_status !== 'active') {
            return false;
        }
        
        if ($this->license_expires_at && strtotime($this->license_expires_at) < time()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get tenant users
     */
    public function getUsers() {
        try {
            return $this->db->fetchAll(
                "SELECT id, name, email, phone, department, position, status, created_at 
                 FROM users 
                 WHERE tenant_id = ? 
                 ORDER BY name",
                [$this->id]
            );
        } catch (Exception $e) {
            throw new Exception('Failed to get tenant users: ' . $e->getMessage());
        }
    }
    
    /**
     * Get tenant statistics
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // User count
            $userCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM users WHERE tenant_id = ?",
                [$this->id]
            );
            $stats['users'] = $userCount['count'];
            
            // Customer count (if customers table exists)
            try {
                $customerCount = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM customers WHERE tenant_id = ?",
                    [$this->id]
                );
                $stats['customers'] = $customerCount['count'];
            } catch (Exception $e) {
                $stats['customers'] = 0;
            }
            
            // Product count (if products table exists)
            try {
                $productCount = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM products WHERE tenant_id = ?",
                    [$this->id]
                );
                $stats['products'] = $productCount['count'];
            } catch (Exception $e) {
                $stats['products'] = 0;
            }
            
            return $stats;
        } catch (Exception $e) {
            return [
                'users' => 0,
                'customers' => 0,
                'products' => 0
            ];
        }
    }
    
    /**
     * Update last login time
     */
    public function updateLastLogin() {
        try {
            $this->db->query(
                "UPDATE tenants SET last_login_at = NOW() WHERE id = ?",
                [$this->id]
            );
        } catch (Exception $e) {
            // Log error but don't throw exception
            error_log('Failed to update tenant last login: ' . $e->getMessage());
        }
    }
    
    // Getter methods
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPhone() { return $this->phone; }
    public function getAddress() { return $this->address; }
    public function getLicenseKey() { return $this->license_key; }
    public function getLicenseType() { return $this->license_type; }
    public function getLicenseExpiresAt() { return $this->license_expires_at; }
    public function getStatus() { return $this->status; }
    public function getMaxUsers() { return $this->max_users; }
    public function getCurrentUsers() { return $this->current_users; }
    public function getMaxWarehouses() { return $this->max_warehouses; }
    public function getCurrentWarehouses() { return $this->current_warehouses; }
    public function getEnabledModules() { return $this->enabled_modules; }
    public function getAdminUserId() { return $this->admin_user_id; }
    public function getAdminName() { return $this->admin_name; }
    public function getAdminEmail() { return $this->admin_email; }
    public function getLastLoginAt() { return $this->last_login_at; }
    public function getBillingStatus() { return $this->billing_status; }
    
    /**
     * Get tenant data as array
     */
    public function getTenantData() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'license_key' => $this->license_key,
            'license_type' => $this->license_type,
            'license_expires_at' => $this->license_expires_at,
            'status' => $this->status,
            'max_users' => $this->max_users,
            'current_users' => $this->current_users,
            'max_warehouses' => $this->max_warehouses,
            'current_warehouses' => $this->current_warehouses,
            'enabled_modules' => $this->enabled_modules,
            'admin_user_id' => $this->admin_user_id,
            'admin_name' => $this->admin_name,
            'admin_email' => $this->admin_email,
            'last_login_at' => $this->last_login_at,
            'billing_status' => $this->billing_status
        ];
    }
}
