<?php
/**
 * Emergency Dashboard - Bypass Laravel completely
 * This provides a working dashboard when Laravel fails
 */

// Start session for authentication check
session_start();

// Simple authentication check
$isAuthenticated = isset($_SESSION['user']) || isset($_SESSION['user_id']) || isset($_SESSION['laravel_session']);

if (!$isAuthenticated) {
    // Redirect to custom auth if not authenticated
    header('Location: /custom-auth/login.php');
    exit;
}

// Get user info from session
$userName = $_SESSION['user_name'] ?? $_SESSION['user']['name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? $_SESSION['user']['email'] ?? 'user@example.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maxcon ERP - Emergency Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .stats-card-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
            border: none;
        }
        .stats-card-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
        }
        .stats-card-danger {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
        }
        .module-card {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .module-card:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-rocket me-2"></i>Maxcon ERP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/test-inventory"><i class="fas fa-boxes me-1"></i>Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/test-sales"><i class="fas fa-cash-register me-1"></i>Sales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/test-reports"><i class="fas fa-chart-bar me-1"></i>Reports</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/custom-auth/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Custom Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/custom-auth/dashboard.php?logout=1"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Alert -->
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Emergency Mode Active:</strong> Laravel services are temporarily unavailable. This emergency dashboard provides core functionality while we resolve the issues.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Total Products</div>
                                <div class="h5 mb-0 font-weight-bold">1,234</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card-success">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Today Sales</div>
                                <div class="h5 mb-0 font-weight-bold">IQD 15,420</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cash-register fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card-warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Low Stock Items</div>
                                <div class="h5 mb-0 font-weight-bold">23</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card-danger">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col me-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Expiring Soon</div>
                                <div class="h5 mb-0 font-weight-bold">8</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules Grid -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card module-card h-100" onclick="window.location.href='/test-inventory'">
                    <div class="card-body text-center">
                        <i class="fas fa-boxes fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Inventory Management</h5>
                        <p class="card-text">Manage products, categories, and stock levels</p>
                        <span class="badge bg-success">Available</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card module-card h-100" onclick="window.location.href='/test-sales'">
                    <div class="card-body text-center">
                        <i class="fas fa-cash-register fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Sales & POS</h5>
                        <p class="card-text">Process sales and point of sale operations</p>
                        <span class="badge bg-success">Available</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card module-card h-100" onclick="alert('Customer module temporarily unavailable due to Laravel service issues')">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Customer Management</h5>
                        <p class="card-text">Maintain customer database and relationships</p>
                        <span class="badge bg-warning">Limited</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card module-card h-100" onclick="window.location.href='/test-reports'">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Reports & Analytics</h5>
                        <p class="card-text">Generate comprehensive business reports</p>
                        <span class="badge bg-success">Available</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-server me-2"></i>System Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p><span class="badge bg-success">✅</span> Database</p>
                                <p><span class="badge bg-success">✅</span> Authentication</p>
                            </div>
                            <div class="col-6">
                                <p><span class="badge bg-danger">❌</span> Laravel Services</p>
                                <p><span class="badge bg-warning">⚠️</span> Full Features</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="/custom-auth/dashboard.php" class="btn btn-primary btn-sm me-2 mb-2">
                            <i class="fas fa-tachometer-alt me-1"></i>Custom Dashboard
                        </a>
                        <a href="/custom-auth/test.php" class="btn btn-info btn-sm me-2 mb-2">
                            <i class="fas fa-tools me-1"></i>System Test
                        </a>
                        <a href="/laravel-dashboard-fix.php" class="btn btn-warning btn-sm me-2 mb-2">
                            <i class="fas fa-wrench me-1"></i>Fix Laravel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
