<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Master Admin') - {{ config('app.name', 'MAXCON ERP') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --master-primary: #1e3c72;
            --master-secondary: #2a5298;
            --master-accent: #f8f9fa;
            --master-dark: #343a40;
            --master-light: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .master-sidebar {
            background: linear-gradient(180deg, var(--master-primary) 0%, var(--master-secondary) 100%);
            min-height: 100vh;
            width: 280px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .master-sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-brand {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        .master-content {
            margin-left: 280px;
            transition: all 0.3s ease;
        }
        
        .master-content.expanded {
            margin-left: 80px;
        }
        
        .master-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }
        
        .master-navbar .navbar-brand {
            font-weight: bold;
            color: var(--master-primary);
        }
        
        .stats-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .border-left-primary {
            border-left: 4px solid var(--master-primary) !important;
        }
        
        .border-left-success {
            border-left: 4px solid #28a745 !important;
        }
        
        .border-left-info {
            border-left: 4px solid #17a2b8 !important;
        }
        
        .border-left-warning {
            border-left: 4px solid #ffc107 !important;
        }
        
        .text-xs {
            font-size: 0.75rem;
        }
        
        .font-weight-bold {
            font-weight: 700;
        }
        
        .text-gray-800 {
            color: #5a5c69;
        }
        
        .text-gray-300 {
            color: #dddfeb;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--master-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .system-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #28a745;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .master-footer {
            background: white;
            padding: 1rem 2rem;
            margin-top: 3rem;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="master-sidebar" id="masterSidebar">
        <div class="sidebar-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a href="{{ route('central.dashboard') }}" class="sidebar-brand d-block mt-2">
                <i class="fas fa-crown me-2"></i>
                <span class="brand-text">MASTER ADMIN</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('central.dashboard') }}" class="nav-link {{ request()->routeIs('central.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">{{ __('Dashboard') }}</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('central.tenants.index') }}" class="nav-link {{ request()->routeIs('central.tenants.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span class="nav-text">{{ __('Tenants') }}</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('central.users.index') }}" class="nav-link {{ request()->routeIs('central.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">{{ __('Users') }}</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('central.licenses.index') }}" class="nav-link {{ request()->routeIs('central.licenses.*') ? 'active' : '' }}">
                    <i class="fas fa-key"></i>
                    <span class="nav-text">{{ __('Licenses') }}</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('central.system.info') }}" class="nav-link {{ request()->routeIs('central.system.*') ? 'active' : '' }}">
                    <i class="fas fa-server"></i>
                    <span class="nav-text">{{ __('System') }}</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('central.settings.index') }}" class="nav-link {{ request()->routeIs('central.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">{{ __('Settings') }}</span>
                </a>
            </div>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">
            
            <div class="nav-item">
                <form method="POST" action="{{ route('central.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-text">{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="master-content" id="masterContent">
        <!-- Top Navbar -->
        <nav class="master-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h4 class="mb-0">@yield('page-title', 'Master Admin')</h4>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <!-- System Status -->
                <div class="system-status">
                    <div class="status-indicator"></div>
                    <small class="text-muted">{{ __('System Online') }}</small>
                </div>
                
                <!-- User Info -->
                <div class="user-dropdown dropdown">
                    <button class="btn btn-link dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span>{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ auth()->user()->email }}</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('central.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Page Content -->
        <div class="container-fluid px-4">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <footer class="master-footer">
            <div class="row">
                <div class="col-md-6 text-start">
                    <small>&copy; {{ date('Y') }} MAXCON ERP. {{ __('All rights reserved.') }}</small>
                </div>
                <div class="col-md-6 text-end">
                    <small>{{ __('Master Admin Panel') }} v2.0.0</small>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('masterSidebar');
            const content = document.getElementById('masterContent');
            
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
            
            // Hide/show text in sidebar
            const navTexts = document.querySelectorAll('.nav-text, .brand-text');
            navTexts.forEach(text => {
                text.style.display = sidebar.classList.contains('collapsed') ? 'none' : 'inline';
            });
        }
        
        // Auto-refresh system status
        setInterval(() => {
            // You can add AJAX call here to check system status
            console.log('System status check...');
        }, 30000);
    </script>
    
    @stack('scripts')
</body>
</html>
