<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Master Admin Login') }} - {{ config('app.name', 'MAXCON ERP') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .master-login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .master-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
        }
        
        .master-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .master-header .content {
            position: relative;
            z-index: 1;
        }
        
        .admin-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .master-body {
            padding: 2.5rem;
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.25rem rgba(42, 82, 152, 0.15);
            background: white;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        
        .form-control.with-icon {
            padding-left: 3rem;
        }
        
        .btn-master {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-master:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(42, 82, 152, 0.3);
        }
        
        .btn-master::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-master:hover::before {
            left: 100%;
        }
        
        .security-badge {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .version-info {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape">
            <i class="fas fa-cog fa-3x"></i>
        </div>
        <div class="shape">
            <i class="fas fa-shield-alt fa-2x"></i>
        </div>
        <div class="shape">
            <i class="fas fa-server fa-2x"></i>
        </div>
    </div>

    <div class="master-login-container">
        <div class="master-header">
            <div class="content">
                <div class="admin-icon">
                    <i class="fas fa-crown fa-2x"></i>
                </div>
                <h2 class="mb-0 fw-bold">{{ __('MASTER ADMIN') }}</h2>
                <p class="mb-0 mt-2 opacity-75">{{ __('System Control Center') }}</p>
            </div>
        </div>
        
        <div class="master-body">
            <div class="text-center mb-4">
                <h4 class="text-dark mb-2">{{ __('Secure Access') }}</h4>
                <p class="text-muted small">{{ __('Enter your master credentials to access the system control panel') }}</p>
            </div>
            
            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('status') }}
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>{{ __('Authentication Failed') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('central.login.post') }}" id="masterLoginForm">
                @csrf

                <!-- Email Address -->
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input id="email" 
                           class="form-control with-icon @error('email') is-invalid @enderror" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="{{ __('Master Admin Email') }}">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input id="password" 
                           class="form-control with-icon @error('password') is-invalid @enderror"
                           type="password"
                           name="password"
                           required 
                           autocomplete="current-password"
                           placeholder="{{ __('Master Password') }}">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-check mb-3">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                    <label for="remember_me" class="form-check-label text-sm">
                        {{ __('Keep me signed in') }}
                    </label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-master text-white">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        {{ __('Access Control Panel') }}
                    </button>
                </div>

                <!-- Security Badge -->
                <div class="security-badge">
                    <i class="fas fa-shield-alt text-success me-2"></i>
                    <small class="text-success">
                        <strong>{{ __('Secure Connection') }}</strong><br>
                        {{ __('This login is protected with advanced security measures') }}
                    </small>
                </div>
            </form>

            <!-- Demo Credentials for Development -->
            @if (app()->environment('local'))
            <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded border border-warning border-opacity-25">
                <h6 class="text-warning mb-2">
                    <i class="fas fa-code me-2"></i>{{ __('Development Mode') }}
                </h6>
                <small class="text-muted">
                    <strong>{{ __('Email') }}:</strong> admin@maxcon.com<br>
                    <strong>{{ __('Password') }}:</strong> password
                </small>
            </div>
            @endif
        </div>
    </div>

    <!-- Version Info -->
    <div class="version-info">
        <i class="fas fa-code-branch me-1"></i>
        v2.0.0 Master
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add loading state to form submission
        document.getElementById('masterLoginForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Authenticating...") }}';
            submitBtn.disabled = true;
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    </script>
    @if (app()->environment('local'))
    <script>
        // Auto-fill demo credentials in development
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            // Add click handler to demo credentials
            const demoSection = document.querySelector('.bg-warning');
            if (demoSection) {
                demoSection.style.cursor = 'pointer';
                demoSection.addEventListener('click', function() {
                    emailInput.value = 'admin@maxcon.com';
                    passwordInput.value = 'password';
                    emailInput.focus();
                });
            }
        });
    </script>
    @endif
</body>
</html>
