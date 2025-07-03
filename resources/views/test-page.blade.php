<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXCON ERP - Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero-section {
            padding: 100px 0;
            text-align: center;
            color: white;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin: 50px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="display-3 fw-bold mb-4">
                <i class="fas fa-hospital-alt me-3"></i>
                MAXCON ERP
            </h1>
            <p class="lead fs-4 mb-5">نظام إدارة الموارد المؤسسية للشركات الطبية العراقية</p>
            <p class="fs-5">Comprehensive ERP System for Iraqi Medical Companies</p>
        </div>

        <!-- Login Section -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        تسجيل الدخول - Login
                    </h3>
                    
                    <div class="alert alert-info" role="alert">
                        <h6><i class="fas fa-info-circle me-2"></i>Demo Login Credentials:</h6>
                        <strong>Email:</strong> admin@maxcon-demo.com<br>
                        <strong>Password:</strong> password
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/login" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Go to Login Page
                        </a>
                        <a href="/suppliers" class="btn btn-outline-secondary">
                            <i class="fas fa-truck me-2"></i>
                            Test Suppliers Page (Requires Login)
                        </a>
                        <a href="/export-test" class="btn btn-outline-success">
                            <i class="fas fa-file-export me-2"></i>
                            Test Export Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>إدارة العملاء</h4>
                    <p>Customer Management</p>
                    <ul class="list-unstyled">
                        <li>✅ Add/Edit/Delete Customers</li>
                        <li>✅ Import from Excel</li>
                        <li>✅ Export Reports</li>
                        <li>✅ Customer Statements</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h4>إدارة الموردين</h4>
                    <p>Supplier Management</p>
                    <ul class="list-unstyled">
                        <li>✅ Add/Edit/Delete Suppliers</li>
                        <li>✅ Import from Excel</li>
                        <li>✅ Export Reports</li>
                        <li>✅ Supplier Evaluation</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4>أوامر الشراء</h4>
                    <p>Purchase Orders</p>
                    <ul class="list-unstyled">
                        <li>✅ Create Purchase Orders</li>
                        <li>✅ Approve & Receive</li>
                        <li>✅ Print Orders</li>
                        <li>✅ Track Status</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Additional Features -->
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h4>إدارة المخزون</h4>
                    <p>Inventory Management</p>
                    <ul class="list-unstyled">
                        <li>✅ Product Management</li>
                        <li>✅ Stock Tracking</li>
                        <li>✅ Low Stock Alerts</li>
                        <li>✅ Expiry Management</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>التقارير</h4>
                    <p>Reports & Analytics</p>
                    <ul class="list-unstyled">
                        <li>✅ Sales Reports</li>
                        <li>✅ Financial Reports</li>
                        <li>✅ Customer Reports</li>
                        <li>✅ Export to Excel/PDF</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="fas fa-language"></i>
                    </div>
                    <h4>متعدد اللغات</h4>
                    <p>Multi-Language Support</p>
                    <ul class="list-unstyled">
                        <li>✅ Arabic (العربية)</li>
                        <li>✅ English</li>
                        <li>✅ Kurdish (کوردی)</li>
                        <li>✅ RTL/LTR Support</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white py-5">
            <h5>🎉 MAXCON ERP - Complete Business Management Solution</h5>
            <p>Built with Laravel, Bootstrap, and modern web technologies</p>
            <p class="mb-0">© 2024 MAXCON ERP. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
