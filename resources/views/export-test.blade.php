<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXCON ERP - Export Test</title>
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
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Header -->
        <div class="text-center text-white mb-5">
            <h1 class="display-4 fw-bold">
                <i class="fas fa-file-export me-3"></i>
                MAXCON ERP - Export Test
            </h1>
            <p class="lead">اختبار تصدير التقارير - Export Reports Test</p>
        </div>

        <!-- Export Test Card -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="test-card">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-download text-success me-2"></i>
                        Suppliers Performance Report Export
                    </h3>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Export Information:</h6>
                        <ul class="mb-0">
                            <li>Export includes 3 sample suppliers</li>
                            <li>Data includes: Name, Contact, Orders, Spending, Rating</li>
                            <li>Summary statistics at the end of file</li>
                            <li>CSV format with UTF-8 encoding</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="{{ route('export.suppliers') }}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="format" class="form-label">Export Format</label>
                                <select class="form-select" id="format" name="format">
                                    <option value="excel">Excel/CSV Format</option>
                                    <option value="pdf">PDF Format (CSV for now)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_range" class="form-label">Date Range (Demo)</label>
                                <select class="form-select" id="date_range" name="date_range">
                                    <option value="last_30_days">Last 30 Days</option>
                                    <option value="last_90_days">Last 90 Days</option>
                                    <option value="this_year">This Year</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-download me-2"></i>
                                Download Suppliers Performance Report
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <h5 class="text-secondary mb-3">
                        <i class="fas fa-table me-2"></i>
                        Report Preview
                    </h5>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Supplier Name</th>
                                    <th>Contact</th>
                                    <th>Type</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Baghdad Medical Supplies</td>
                                    <td>Ali Mohammed</td>
                                    <td><span class="badge bg-success">Distributor</span></td>
                                    <td>45</td>
                                    <td>2,500,000 IQD</td>
                                    <td>
                                        <span class="text-warning">
                                            ★★★★★ 4.5
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Kurdistan Pharmaceuticals</td>
                                    <td>Sara Ahmed</td>
                                    <td><span class="badge bg-primary">Manufacturer</span></td>
                                    <td>32</td>
                                    <td>1,800,000 IQD</td>
                                    <td>
                                        <span class="text-warning">
                                            ★★★★☆ 4.2
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Basra Equipment Co.</td>
                                    <td>Omar Hassan</td>
                                    <td><span class="badge bg-info">Wholesaler</span></td>
                                    <td>28</td>
                                    <td>1,650,000 IQD</td>
                                    <td>
                                        <span class="text-warning">
                                            ★★★★☆ 4.0
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-light mt-3">
                        <h6 class="text-muted">Summary Statistics:</h6>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <strong>3</strong><br>
                                <small class="text-muted">Total Suppliers</small>
                            </div>
                            <div class="col-md-3">
                                <strong>105</strong><br>
                                <small class="text-muted">Total Orders</small>
                            </div>
                            <div class="col-md-3">
                                <strong>5,950,000 IQD</strong><br>
                                <small class="text-muted">Total Spent</small>
                            </div>
                            <div class="col-md-3">
                                <strong>4.2</strong><br>
                                <small class="text-muted">Avg Rating</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <a href="/templates-test" class="btn btn-outline-primary me-2">
                                <i class="fas fa-file-excel me-2"></i>
                                Templates Test
                            </a>
                            <a href="/test" class="btn btn-outline-primary me-2">
                                <i class="fas fa-home me-2"></i>
                                Main Demo
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
            <div class="col-md-8">
                <div class="test-card">
                    <h5 class="text-secondary">
                        <i class="fas fa-cogs me-2"></i>
                        Technical Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info">Export Features:</h6>
                            <ul class="list-unstyled">
                                <li>✅ CSV format with UTF-8 encoding</li>
                                <li>✅ Comprehensive supplier data</li>
                                <li>✅ Summary statistics included</li>
                                <li>✅ Timestamp in filename</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">Data Columns:</h6>
                            <ul class="list-unstyled">
                                <li>• ID, Name, Contact Person</li>
                                <li>• Phone, Email, Type</li>
                                <li>• Orders, Spending, Rating</li>
                                <li>• Status, Last Order Date</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white py-4">
            <p class="mb-0">© 2024 MAXCON ERP - Export Test Page</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Report...';
            button.disabled = true;
            
            // Reset button after delay (in case of error)
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 10000);
        });
    </script>
</body>
</html>
