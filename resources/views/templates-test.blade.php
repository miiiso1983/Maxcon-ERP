<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXCON ERP - Templates Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-test {
            margin: 10px;
            min-width: 200px;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-success { background-color: #28a745; }
        .status-error { background-color: #dc3545; }
        .status-pending { background-color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Header -->
        <div class="text-center text-white mb-5">
            <h1 class="display-4 fw-bold">
                <i class="fas fa-download me-3"></i>
                MAXCON ERP - Templates Test
            </h1>
            <p class="lead">اختبار تحميل قوالب Excel - Templates Download Test</p>
        </div>

        <!-- Templates Test Card -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="test-card">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        Excel Templates Download Test
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-truck me-2"></i>
                                Suppliers Templates (قوالب الموردين)
                            </h5>
                            
                            <div class="d-grid gap-2 mb-4">
                                <a href="/templates/suppliers" class="btn btn-success btn-test" target="_blank">
                                    <i class="fas fa-download me-2"></i>
                                    Download Suppliers Template
                                </a>
                                <button class="btn btn-outline-info btn-test" onclick="testSupplierTemplate()">
                                    <i class="fas fa-vial me-2"></i>
                                    Test Supplier Template Link
                                </button>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Supplier Template Info:</h6>
                                <ul class="mb-0">
                                    <li>12 columns with Arabic/English headers</li>
                                    <li>3 sample supplier records</li>
                                    <li>UTF-8 encoding for Arabic text</li>
                                    <li>Ready for import to system</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-users me-2"></i>
                                Customers Templates (قوالب العملاء)
                            </h5>
                            
                            <div class="d-grid gap-2 mb-4">
                                <a href="/templates/customers" class="btn btn-success btn-test" target="_blank">
                                    <i class="fas fa-download me-2"></i>
                                    Download Customers Template
                                </a>
                                <button class="btn btn-outline-info btn-test" onclick="testCustomerTemplate()">
                                    <i class="fas fa-vial me-2"></i>
                                    Test Customer Template Link
                                </button>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Customer Template Info:</h6>
                                <ul class="mb-0">
                                    <li>12 columns with Arabic/English headers</li>
                                    <li>3 sample customer records</li>
                                    <li>UTF-8 encoding for Arabic text</li>
                                    <li>Ready for import to system</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Test Results -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-secondary">
                                <i class="fas fa-clipboard-check me-2"></i>
                                Test Results (نتائج الاختبار)
                            </h5>
                            <div id="test-results" class="border rounded p-3 bg-light">
                                <p class="text-muted mb-0">Click test buttons above to check template links...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <a href="/export-test" class="btn btn-success me-2">
                                <i class="fas fa-file-export me-2"></i>
                                Test Export Reports
                            </a>
                            <a href="/test" class="btn btn-primary me-2">
                                <i class="fas fa-home me-2"></i>
                                Back to Main Demo
                            </a>
                            <a href="/login" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login to System
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technical Info -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-10">
                <div class="test-card">
                    <h5 class="text-secondary">
                        <i class="fas fa-cogs me-2"></i>
                        Technical Information (معلومات تقنية)
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info">Template URLs:</h6>
                            <ul class="list-unstyled">
                                <li><code>/templates/suppliers</code> - Suppliers template</li>
                                <li><code>/templates/customers</code> - Customers template</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">Features:</h6>
                            <ul class="list-unstyled">
                                <li>✅ No authentication required</li>
                                <li>✅ Direct download links</li>
                                <li>✅ UTF-8 encoding support</li>
                                <li>✅ Sample data included</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white py-4">
            <p class="mb-0">© 2024 MAXCON ERP - Templates Test Page</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addTestResult(message, status) {
            const resultsDiv = document.getElementById('test-results');
            const timestamp = new Date().toLocaleTimeString();
            const statusClass = status === 'success' ? 'status-success' : 
                               status === 'error' ? 'status-error' : 'status-pending';
            
            const resultHtml = `
                <div class="mb-2">
                    <span class="status-indicator ${statusClass}"></span>
                    <strong>[${timestamp}]</strong> ${message}
                </div>
            `;
            
            if (resultsDiv.innerHTML.includes('Click test buttons')) {
                resultsDiv.innerHTML = resultHtml;
            } else {
                resultsDiv.innerHTML += resultHtml;
            }
        }

        function testSupplierTemplate() {
            addTestResult('Testing supplier template link...', 'pending');
            
            fetch('/templates/suppliers')
                .then(response => {
                    if (response.ok) {
                        addTestResult('✅ Supplier template link working! Status: ' + response.status, 'success');
                        return response.text();
                    } else {
                        throw new Error('HTTP ' + response.status);
                    }
                })
                .then(data => {
                    const lines = data.split('\n').length - 1;
                    addTestResult(`📄 Supplier template contains ${lines} lines of data`, 'success');
                })
                .catch(error => {
                    addTestResult('❌ Supplier template test failed: ' + error.message, 'error');
                });
        }

        function testCustomerTemplate() {
            addTestResult('Testing customer template link...', 'pending');
            
            fetch('/templates/customers')
                .then(response => {
                    if (response.ok) {
                        addTestResult('✅ Customer template link working! Status: ' + response.status, 'success');
                        return response.text();
                    } else {
                        throw new Error('HTTP ' + response.status);
                    }
                })
                .then(data => {
                    const lines = data.split('\n').length - 1;
                    addTestResult(`📄 Customer template contains ${lines} lines of data`, 'success');
                })
                .catch(error => {
                    addTestResult('❌ Customer template test failed: ' + error.message, 'error');
                });
        }

        // Auto-test on page load
        window.addEventListener('load', function() {
            setTimeout(() => {
                addTestResult('🚀 Page loaded successfully. Ready for testing!', 'success');
            }, 500);
        });
    </script>
</body>
</html>
