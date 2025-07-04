<?php
require_once 'Session.php';
require_once 'User.php';

Session::init();

// Check if user is logged in
if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user
$currentUser = User::getCurrentUser();
if (!$currentUser) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    User::logout();
    Session::setFlash('success', 'You have been logged out successfully.');
    header('Location: login.php');
    exit;
}

// Get flash messages
$flashMessages = Session::getAllFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Maxcon ERP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 16px;
        }
        
        .user-role {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .welcome-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .welcome-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .module-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .module-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .module-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .module-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-admin {
            background: #fff3cd;
            color: #856404;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">🚀 Maxcon ERP</div>
            <div class="user-info">
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($currentUser->getName()); ?></div>
                    <div class="user-role">
                        <?php echo htmlspecialchars($currentUser->getPosition() ?: 'User'); ?>
                        <?php if ($currentUser->isSuperAdmin()): ?>
                            <span class="status-badge status-admin">Super Admin</span>
                        <?php endif; ?>
                        <span class="status-badge status-active"><?php echo ucfirst($currentUser->getStatus()); ?></span>
                    </div>
                </div>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <?php foreach ($flashMessages as $type => $message): ?>
            <div class="alert alert-<?php echo $type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endforeach; ?>
        
        <div class="welcome-card">
            <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($currentUser->getName()); ?>!</h1>
            <p class="welcome-subtitle">Your custom authentication system is working perfectly. Laravel issues have been bypassed.</p>
            
            <div class="user-stats">
                <div class="stat-item">
                    <div class="stat-label">User ID</div>
                    <div class="stat-value">#<?php echo $currentUser->getId(); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Email</div>
                    <div class="stat-value"><?php echo htmlspecialchars($currentUser->getEmail()); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Department</div>
                    <div class="stat-value"><?php echo htmlspecialchars($currentUser->getDepartment() ?: 'Not Set'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Tenant ID</div>
                    <div class="stat-value"><?php echo htmlspecialchars($currentUser->getTenantId() ?: 'None'); ?></div>
                </div>
            </div>
        </div>
        
        <div class="modules-grid">
            <div class="module-card" onclick="alert('Module integration coming soon!')">
                <div class="module-icon">📦</div>
                <div class="module-title">Inventory Management</div>
                <div class="module-description">Manage products, categories, warehouses, and stock levels with real-time tracking.</div>
            </div>
            
            <div class="module-card" onclick="alert('Module integration coming soon!')">
                <div class="module-icon">💰</div>
                <div class="module-title">Sales & POS</div>
                <div class="module-description">Process sales, manage point of sale operations, and track revenue.</div>
            </div>
            
            <div class="module-card" onclick="alert('Module integration coming soon!')">
                <div class="module-icon">👥</div>
                <div class="module-title">Customer Management</div>
                <div class="module-description">Maintain customer database, track interactions, and manage relationships.</div>
            </div>
            
            <div class="module-card" onclick="alert('Module integration coming soon!')">
                <div class="module-icon">📊</div>
                <div class="module-title">Financial Reports</div>
                <div class="module-description">Generate comprehensive financial reports and analytics.</div>
            </div>
            
            <div class="module-card" onclick="alert('Module integration coming soon!')">
                <div class="module-icon">👨‍💼</div>
                <div class="module-title">HR Management</div>
                <div class="module-description">Employee management, attendance tracking, and payroll processing.</div>
            </div>
            
            <div class="module-card" onclick="alert('Module integration coming soon!')">
                <div class="module-icon">🤖</div>
                <div class="module-title">AI Analytics</div>
                <div class="module-description">AI-powered insights, demand forecasting, and customer analytics.</div>
            </div>
        </div>
    </div>
</body>
</html>
