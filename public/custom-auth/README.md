# Custom PHP Authentication System

## 🎯 Overview

This custom PHP authentication system was created to bypass Laravel configuration issues while maintaining full compatibility with your existing database structure. It provides secure login, session management, multi-tenant support, and role-based access control.

## ✅ Features

- **Secure Authentication**: Password verification using PHP's `password_verify()` (compatible with Laravel's bcrypt)
- **Session Management**: Secure session handling with timeout and regeneration
- **Multi-tenant Support**: Tenant isolation and access control
- **Role-based Access Control**: Super admin detection and permission management
- **CSRF Protection**: Built-in CSRF token generation and verification
- **Responsive Design**: Mobile-friendly login and dashboard interfaces
- **Database Integration**: Works with existing Laravel database structure

## 📁 File Structure

```
custom-auth/
├── Database.php      # Database connection handler
├── User.php          # User authentication and management
├── Tenant.php        # Multi-tenant support
├── Session.php       # Session management utilities
├── Auth.php          # Authentication and authorization helpers
├── login.php         # Login interface
├── dashboard.php     # Protected dashboard
├── index.php         # Entry point (redirects to login)
└── README.md         # This documentation
```

## 🚀 Quick Start

### 1. Access the System

Visit: `https://your-domain.com/custom-auth/`

### 2. Login Credentials

**Demo Account:**
- Email: `admin@maxcon-demo.com`
- Password: `password`

### 3. Database Requirements

The system works with your existing database structure:
- `users` table with Laravel-compatible password hashing
- `tenants` table for multi-tenant support
- Proper `.env` file with database credentials

## 🔧 Configuration

### Environment Variables

Ensure your `.env` file contains:

```env
DB_HOST=127.0.0.1
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Database Tables

The system uses these existing tables:
- `users` - User accounts and authentication
- `tenants` - Multi-tenant organization data
- `sessions` - Session storage (optional)

## 🛡️ Security Features

### Password Security
- Uses `password_verify()` for Laravel bcrypt compatibility
- Secure password hashing verification

### Session Security
- HTTP-only cookies
- Secure cookies (HTTPS)
- Session regeneration on login
- Automatic timeout (2 hours)
- CSRF token protection

### Access Control
- Multi-tenant isolation
- Role-based permissions
- Super admin privileges
- Module access control

## 👥 User Roles

### Super Admin
- Full system access
- All tenant access
- All module permissions
- User management

### Admin
- Tenant-specific access
- Full CRUD operations
- User management within tenant
- Settings access

### Manager
- Limited CRUD operations
- Report access
- Customer/product management

### User
- Read-only access
- Basic sales operations
- Limited permissions

## 🔐 API Usage

### Authentication Check
```php
require_once 'Auth.php';

// Check if user is logged in
if (Auth::check()) {
    $user = Auth::user();
    echo "Welcome, " . $user->getName();
}
```

### Permission Check
```php
// Check module access
if (Auth::canAccessModule('inventory')) {
    // Show inventory module
}

// Check specific action
if (Auth::canPerformAction('create', 'products')) {
    // Show create product button
}
```

### Tenant Access
```php
// Get current tenant
$tenant = Auth::getTenant();
if ($tenant) {
    echo "Tenant: " . $tenant->getName();
}

// Check tenant access
if (Auth::hasAccessToTenant($tenantId)) {
    // Allow access
}
```

## 🔄 Session Management

### Manual Session Control
```php
require_once 'Session.php';

// Set session data
Session::set('key', 'value');

// Get session data
$value = Session::get('key', 'default');

// Flash messages
Session::setFlash('success', 'Operation completed!');
$message = Session::getFlash('success');

// CSRF protection
$token = Session::setCsrfToken();
$isValid = Session::verifyCsrfToken($token);
```

## 🎨 Customization

### Styling
The login and dashboard pages use inline CSS for easy customization. Modify the `<style>` sections in:
- `login.php` - Login page styling
- `dashboard.php` - Dashboard styling

### Permissions
Edit the permissions array in `Auth.php` to customize role-based access:

```php
$permissions = [
    'admin' => [
        'users' => ['create', 'read', 'update', 'delete'],
        'products' => ['create', 'read', 'update', 'delete'],
        // Add more resources and actions
    ],
    // Add more roles
];
```

## 🔍 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check `.env` file exists and has correct credentials
   - Verify database server is running
   - Ensure PHP PDO MySQL extension is installed

2. **Login Failed**
   - Verify user exists in database
   - Check password is hashed with bcrypt
   - Ensure user status is 'active'

3. **Session Issues**
   - Check PHP session configuration
   - Verify write permissions on session directory
   - Ensure cookies are enabled

### Debug Mode

Add this to the top of any file for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🚀 Integration with Laravel

This system can coexist with Laravel:
- Uses same database structure
- Compatible password hashing
- Can be integrated into existing Laravel routes
- Maintains session compatibility

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section
2. Verify database connectivity
3. Ensure all files are uploaded correctly
4. Check PHP error logs

## 🎉 Success!

Your custom authentication system is now ready! It bypasses all Laravel configuration issues while providing:
- ✅ Secure login/logout
- ✅ Session management
- ✅ Multi-tenant support
- ✅ Role-based access control
- ✅ CSRF protection
- ✅ Responsive design

Visit `https://your-domain.com/custom-auth/` to start using the system!
