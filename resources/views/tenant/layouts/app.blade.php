<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'ERP Dashboard')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Custom Select2 Styles -->
    <style>
        /* RTL Support for Select2 */
        [dir="rtl"] .select2-container--bootstrap-5 .select2-selection {
            text-align: right;
        }

        [dir="rtl"] .select2-container--bootstrap-5 .select2-selection__arrow {
            left: 12px;
            right: auto;
        }

        [dir="rtl"] .select2-container--bootstrap-5 .select2-selection__clear {
            float: left;
            margin-right: auto;
            margin-left: 12px;
        }

        /* Custom styling for Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
            border-color: #dee2e6;
            min-height: 38px;
        }

        .select2-container--bootstrap-5 .select2-selection:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: 0.375rem;
            border-color: #dee2e6;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #0d6efd;
            color: white;
        }

        .select2-container--bootstrap-5 .select2-search__field {
            border-radius: 0.25rem;
            border-color: #dee2e6;
        }

        /* Custom option styling */
        .select2-results__option .fas {
            color: #6c757d;
            width: 16px;
        }

        /* Loading state */
        .select2-container--bootstrap-5 .select2-results__message {
            color: #6c757d;
            font-style: italic;
        }

        /* Error state */
        .is-invalid + .select2-container--bootstrap-5 .select2-selection {
            border-color: #dc3545;
        }

        .is-invalid + .select2-container--bootstrap-5 .select2-selection:focus-within {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }

        /* Success state */
        .is-valid + .select2-container--bootstrap-5 .select2-selection {
            border-color: #198754;
        }

        .is-valid + .select2-container--bootstrap-5 .select2-selection:focus-within {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        /* Disabled state */
        .select2-container--bootstrap-5 .select2-selection--single[aria-disabled="true"] {
            background-color: #e9ecef;
            opacity: 1;
        }

        /* Multiple select styling */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            border-radius: 0.25rem;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            opacity: 0.8;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            opacity: 1;
        }
    </style>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.15);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">{{ __('app.name') }}</h4>
                        <small class="text-white-50">{{ __('ERP System') }}</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('tenant.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                {{ __('app.dashboard') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('inventory.index') }}">
                                <i class="fas fa-boxes me-2"></i>
                                {{ __('app.inventory') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('sales.index') }}">
                                <i class="fas fa-cash-register me-2"></i>
                                {{ __('app.sales') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('customers.index') }}">
                                <i class="fas fa-users me-2"></i>
                                {{ __('app.customers') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('suppliers.index') }}">
                                <i class="fas fa-truck me-2"></i>
                                {{ __('app.suppliers') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('financial.collections.index') }}">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                {{ __('Collections') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('financial.accounting.dashboard') }}">
                                <i class="fas fa-calculator me-2"></i>
                                {{ __('Accounting') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('reports.dashboard') }}">
                                <i class="fas fa-chart-bar me-2"></i>
                                {{ __('Reports') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('ai.dashboard') }}">
                                <i class="fas fa-brain me-2"></i>
                                {{ __('AI Tools') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('hr.dashboard') }}">
                                <i class="fas fa-user-tie me-2"></i>
                                {{ __('Human Resources') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('medical-reps.dashboard') }}">
                                <i class="fas fa-user-md me-2"></i>
                                {{ __('Medical Reps') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('compliance.dashboard') }}">
                                <i class="fas fa-shield-alt me-2"></i>
                                {{ __('Compliance') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('whatsapp.dashboard') }}">
                                <i class="fab fa-whatsapp me-2"></i>
                                {{ __('WhatsApp') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('performance.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                {{ __('Performance') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('testing.dashboard') }}">
                                <i class="fas fa-vial me-2"></i>
                                {{ __('Testing') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top Navigation -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- Language Switcher -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-globe me-2"></i>Language
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?lang=en">🇺🇸 English</a></li>
                                <li><a class="dropdown-item" href="?lang=ar">🇮🇶 العربية</a></li>
                                <li><a class="dropdown-item" href="?lang=ku">🇮🇶 کوردی</a></li>
                            </ul>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i>User
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Custom Select2 Configuration -->
    <script src="{{ asset('js/select2-config.js') }}"></script>

    <!-- Custom Select2 Initialization -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all select elements
            initializeSelect2();

            // Re-initialize Select2 when new elements are added dynamically
            $(document).on('DOMNodeInserted', function(e) {
                if ($(e.target).is('select') || $(e.target).find('select').length) {
                    setTimeout(function() {
                        initializeSelect2();
                    }, 100);
                }
            });
        });

        function initializeSelect2() {
            // Initialize all select elements that don't already have Select2
            $('select:not(.select2-hidden-accessible)').each(function() {
                var $select = $(this);

                // Skip if already initialized or has specific class to exclude
                if ($select.hasClass('no-select2') || $select.data('select2')) {
                    return;
                }

                var options = {
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $select.attr('placeholder') || $select.find('option:first').text() || '{{ __("Select an option") }}',
                    allowClear: !$select.prop('required'),
                    language: {
                        noResults: function() {
                            return '{{ __("No results found") }}';
                        },
                        searching: function() {
                            return '{{ __("Searching...") }}';
                        },
                        loadingMore: function() {
                            return '{{ __("Loading more results...") }}';
                        }
                    }
                };

                // Add search functionality for selects with more than 5 options
                if ($select.find('option').length > 5) {
                    options.minimumResultsForSearch = 0;
                } else {
                    options.minimumResultsForSearch = Infinity;
                }

                // Special handling for customer selects
                if ($select.attr('id') && $select.attr('id').includes('customer')) {
                    options.templateResult = formatCustomerOption;
                    options.templateSelection = formatCustomerSelection;
                }

                // Special handling for product selects
                if ($select.attr('name') && $select.attr('name').includes('product')) {
                    options.templateResult = formatProductOption;
                    options.templateSelection = formatProductSelection;
                }

                $select.select2(options);
            });
        }

        // Custom formatting for customer options
        function formatCustomerOption(option) {
            if (!option.id) {
                return option.text;
            }

            var $option = $(
                '<span><i class="fas fa-user me-2"></i>' + option.text + '</span>'
            );
            return $option;
        }

        function formatCustomerSelection(option) {
            return option.text;
        }

        // Custom formatting for product options
        function formatProductOption(option) {
            if (!option.id) {
                return option.text;
            }

            var $option = $(
                '<span><i class="fas fa-box me-2"></i>' + option.text + '</span>'
            );
            return $option;
        }

        function formatProductSelection(option) {
            return option.text;
        }

        // Function to refresh Select2 (useful for dynamic content)
        function refreshSelect2() {
            $('.select2-hidden-accessible').select2('destroy');
            initializeSelect2();
        }

        // Make function globally available
        window.refreshSelect2 = refreshSelect2;
        window.initializeSelect2 = initializeSelect2;
    </script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
