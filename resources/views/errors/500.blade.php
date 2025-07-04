<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - Maxcon ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: white;
        }
        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-server"></i>
        </div>
        
        <h1 class="error-title">Server Error</h1>
        
        <p class="error-message">
            {{ $message ?? 'We encountered an internal server error. Our team has been notified and is working to resolve this issue.' }}
        </p>
        
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="{{ url('/') }}" class="btn btn-home">
                <i class="fas fa-home me-2"></i>Go Home
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </a>
        </div>
        
        @if(config('app.debug') && isset($exception))
        <div class="error-details">
            <strong>Error Details:</strong><br>
            {{ $exception->getMessage() }}<br>
            <small>File: {{ $exception->getFile() }} (Line: {{ $exception->getLine() }})</small>
        </div>
        @endif
        
        <div class="mt-4">
            <small class="text-muted">
                Error ID: {{ Str::random(8) }} | 
                Time: {{ now()->format('Y-m-d H:i:s') }}
            </small>
        </div>
    </div>

    @if(!config('app.debug'))
    <script>
        // Auto-refresh after 30 seconds if not in debug mode
        setTimeout(function() {
            if (confirm('Would you like to try refreshing the page?')) {
                window.location.reload();
            }
        }, 30000);
    </script>
    @endif
</body>
</html>
