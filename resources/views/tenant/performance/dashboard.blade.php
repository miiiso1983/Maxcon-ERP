@extends('tenant.layouts.app')

@section('title', __('Performance Optimization'))
@section('page-title', __('Performance Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('Performance') }}</li>
@endsection

@section('content')
<!-- Performance Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Response Time') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $performanceSummary['average_response_time'] ?? 0 }}ms</div>
                        <small class="text-muted">{{ __('Average (24h)') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tachometer-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Cache Hit Rate') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($performanceSummary['cache_hit_rate'] ?? 0, 1) }}%</div>
                        <small class="text-muted">{{ __('Last 24 hours') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-memory fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Database Health') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $databaseHealth['score'] ?? 0 }}/100</div>
                        <small class="text-muted text-{{ $databaseHealth['status'] === 'excellent' ? 'success' : ($databaseHealth['status'] === 'good' ? 'info' : 'warning') }}">
                            {{ ucfirst($databaseHealth['status'] ?? 'unknown') }}
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-database fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Memory Usage') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $systemMetrics['memory']['usage_percent'] ?? 0 }}%</div>
                        <small class="text-muted">{{ $systemMetrics['memory']['used_formatted'] ?? '0 MB' }} / {{ $systemMetrics['memory']['limit_formatted'] ?? '0 MB' }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-microchip fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Alerts -->
@if(!empty($alerts) && count($alerts) > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="m-0">
                    <i class="fas fa-exclamation-triangle {{ marginEnd('2') }}"></i>
                    {{ __('Performance Alerts') }}
                    <span class="badge bg-dark {{ marginStart('2') }}">{{ count($alerts) }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($alerts as $alert)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="{{ marginEnd('3') }}">
                                <i class="fas fa-{{ $alert['severity'] === 'critical' ? 'exclamation-circle text-danger' : 'exclamation-triangle text-warning' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small"><strong>{{ ucfirst($alert['type']) }}</strong></div>
                                <small class="text-muted">{{ $alert['message'] }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $alert['severity'] === 'critical' ? 'danger' : 'warning' }}">
                                    {{ ucfirst($alert['severity']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Redis Monitoring -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">
                    <i class="fab fa-redis {{ marginEnd('2') }} text-danger"></i>
                    {{ __('Redis Cache Monitoring') }}
                </h6>
                <button class="btn btn-outline-primary btn-sm" onclick="refreshRedisData()">
                    <i class="fas fa-sync-alt"></i> {{ __('Refresh') }}
                </button>
            </div>
            <div class="card-body">
                <div class="row" id="redis-monitoring-data">
                    <div class="col-md-3 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-server fa-2x text-success mb-2"></i>
                                <h6 class="small">{{ __('Redis Status') }}</h6>
                                <span class="badge bg-success" id="redis-status">{{ __('Connected') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-memory fa-2x text-info mb-2"></i>
                                <h6 class="small">{{ __('Memory Usage') }}</h6>
                                <div class="h6 mb-0" id="redis-memory">--</div>
                                <small class="text-muted" id="redis-memory-percent">--</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-key fa-2x text-warning mb-2"></i>
                                <h6 class="small">{{ __('Total Keys') }}</h6>
                                <div class="h6 mb-0" id="redis-keys">--</div>
                                <small class="text-muted">{{ __('Stored keys') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                <h6 class="small">{{ __('Hit Ratio') }}</h6>
                                <div class="h6 mb-0" id="redis-hit-ratio">--</div>
                                <small class="text-muted">{{ __('Cache efficiency') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>{{ __('Performance Metrics') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody id="redis-performance-metrics">
                                    <tr>
                                        <td>{{ __('Operations/sec') }}</td>
                                        <td class="text-end" id="redis-ops-sec">--</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Connected Clients') }}</td>
                                        <td class="text-end" id="redis-clients">--</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Uptime') }}</td>
                                        <td class="text-end" id="redis-uptime">--</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('Key Distribution') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody id="redis-key-distribution">
                                    <tr>
                                        <td>{{ __('Cache Keys') }}</td>
                                        <td class="text-end" id="redis-cache-keys">--</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Session Keys') }}</td>
                                        <td class="text-end" id="redis-session-keys">--</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Queue Keys') }}</td>
                                        <td class="text-end" id="redis-queue-keys">--</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Performance Optimization Tools') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-broom fa-3x text-primary mb-3"></i>
                                <h6>{{ __('Clear Cache') }}</h6>
                                <p class="text-muted small">{{ __('Clear application cache for fresh data') }}</p>
                                <button class="btn btn-primary btn-sm" onclick="clearCache('all')">
                                    {{ __('Clear All Cache') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-fire fa-3x text-success mb-3"></i>
                                <h6>{{ __('Warm Up Cache') }}</h6>
                                <p class="text-muted small">{{ __('Pre-load critical data into cache') }}</p>
                                <button class="btn btn-success btn-sm" onclick="warmUpCache()">
                                    {{ __('Warm Up Cache') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-3x text-info mb-3"></i>
                                <h6>{{ __('Optimize Database') }}</h6>
                                <p class="text-muted small">{{ __('Optimize database tables and cleanup') }}</p>
                                <button class="btn btn-info btn-sm" onclick="optimizeDatabase()">
                                    {{ __('Optimize DB') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-magic fa-3x text-warning mb-3"></i>
                                <h6>{{ __('Auto Optimize') }}</h6>
                                <p class="text-muted small">{{ __('Run all optimization tasks automatically') }}</p>
                                <button class="btn btn-warning btn-sm" onclick="autoOptimize()">
                                    {{ __('Auto Optimize') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Redis-specific Actions -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="mb-3">
                            <i class="fab fa-redis text-danger {{ marginEnd('2') }}"></i>
                            {{ __('Redis Cache Actions') }}
                        </h6>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-danger">
                            <div class="card-body text-center">
                                <i class="fab fa-redis fa-3x text-danger mb-3"></i>
                                <h6>{{ __('Clear Redis Cache') }}</h6>
                                <p class="text-muted small">{{ __('Clear all Redis cache data') }}</p>
                                <button class="btn btn-danger btn-sm" onclick="clearRedisCache()">
                                    {{ __('Clear Redis') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-fire fa-3x text-success mb-3"></i>
                                <h6>{{ __('Warm Up Redis') }}</h6>
                                <p class="text-muted small">{{ __('Pre-load frequently used data') }}</p>
                                <button class="btn btn-success btn-sm" onclick="warmUpRedisCache()">
                                    {{ __('Warm Up') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                                <h6>{{ __('Redis Monitoring') }}</h6>
                                <p class="text-muted small">{{ __('View detailed Redis statistics') }}</p>
                                <button class="btn btn-info btn-sm" onclick="refreshRedisData()">
                                    {{ __('Refresh Data') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Metrics & Cache Statistics -->
<div class="row mb-4">
    <!-- System Metrics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('System Metrics') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $systemMetrics['memory']['usage_percent'] ?? 0 }}%</h4>
                            <small class="text-muted">{{ __('Memory Usage') }}</small>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $systemMetrics['memory']['usage_percent'] ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <h4 class="text-info">{{ $systemMetrics['disk']['usage_percent'] ?? 0 }}%</h4>
                            <small class="text-muted">{{ __('Disk Usage') }}</small>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ $systemMetrics['disk']['usage_percent'] ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <strong>{{ $systemMetrics['php_version'] ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ __('PHP Version') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <strong>{{ $systemMetrics['laravel_version'] ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ __('Laravel Version') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        @if(isset($systemMetrics['load_average']['available']) && $systemMetrics['load_average']['available'])
                            <strong>{{ number_format($systemMetrics['load_average']['1min'] ?? 0, 2) }}</strong>
                            <br>
                            <small class="text-muted">{{ __('Load Average') }}</small>
                        @else
                            <strong>N/A</strong>
                            <br>
                            <small class="text-muted">{{ __('Load Average') }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Statistics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Cache Statistics') }}</h6>
            </div>
            <div class="card-body">
                @if(!empty($cacheStats))
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <h4 class="text-success">{{ ucfirst($cacheStats['driver'] ?? 'unknown') }}</h4>
                        <small class="text-muted">{{ __('Cache Driver') }}</small>
                    </div>
                    <div class="col-6">
                        @if(isset($cacheStats['hit_rate']))
                            <h4 class="text-info">{{ $cacheStats['hit_rate'] }}%</h4>
                            <small class="text-muted">{{ __('Hit Rate') }}</small>
                        @else
                            <h4 class="text-muted">N/A</h4>
                            <small class="text-muted">{{ __('Hit Rate') }}</small>
                        @endif
                    </div>
                </div>

                @if($cacheStats['driver'] === 'redis')
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <strong>{{ $cacheStats['total_keys'] ?? 0 }}</strong>
                            <br>
                            <small class="text-muted">{{ __('Total Keys') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <strong>{{ $cacheStats['memory_used'] ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ __('Memory Used') }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <strong>{{ number_format($cacheStats['hits'] ?? 0) }}</strong>
                        <br>
                        <small class="text-muted">{{ __('Cache Hits') }}</small>
                    </div>
                </div>
                @elseif($cacheStats['driver'] === 'file')
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <strong>{{ $cacheStats['total_files'] ?? 0 }}</strong>
                            <br>
                            <small class="text-muted">{{ __('Cache Files') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <strong>{{ $cacheStats['total_size'] ?? 'N/A' }}</strong>
                        <br>
                        <small class="text-muted">{{ __('Total Size') }}</small>
                    </div>
                </div>
                @endif
                @else
                <div class="text-center py-4">
                    <i class="fas fa-memory fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('Cache statistics not available') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Optimization Recommendations -->
@if(!empty($recommendations) && count($recommendations) > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Optimization Recommendations') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Recommendation') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recommendations as $recommendation)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($recommendation['category']) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $recommendation['priority'] === 'high' ? 'danger' : ($recommendation['priority'] === 'medium' ? 'warning' : 'info') }}">
                                        {{ ucfirst($recommendation['priority']) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $recommendation['title'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $recommendation['description'] }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $recommendation['action'] }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
console.log('Performance dashboard JavaScript loaded');

// Clear cache function
function clearCache(type) {
    console.log('clearCache called with type:', type);

    if (!confirm('{{ __("Are you sure you want to clear the cache?") }}')) {
        return;
    }

    try {
        fetch('{{ route("performance.cache.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ cache_type: type })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.results.message);
            location.reload();
        } else {
            alert('{{ __("Error") }}: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred while clearing cache") }}');
    });
    } catch (error) {
        console.error('Error in clearCache:', error);
        alert('Error: ' + error.message);
    }
}

// Warm up cache function
function warmUpCache() {
    const button = event.target;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Warming up...") }}';

    fetch('{{ route("performance.cache.warmup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('{{ __("Error") }}: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred while warming up cache") }}');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '{{ __("Warm Up Cache") }}';
    });
}

// Optimize database function
function optimizeDatabase() {
    if (!confirm('{{ __("Are you sure you want to optimize the database?") }}')) {
        return;
    }

    const button = event.target;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Optimizing...") }}';

    fetch('{{ route("performance.database.optimize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ action: 'optimize_tables' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('{{ __("Database optimization completed successfully") }}');
            location.reload();
        } else {
            alert('{{ __("Error") }}: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred during database optimization") }}');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '{{ __("Optimize DB") }}';
    });
}

// Auto optimize function
function autoOptimize() {
    if (!confirm('{{ __("Are you sure you want to run all optimization tasks?") }}')) {
        return;
    }

    const button = event.target;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Optimizing...") }}';

    fetch('{{ route("performance.optimize") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message + '\n\n' + data.optimizations.join('\n'));
            location.reload();
        } else {
            alert('{{ __("Error") }}: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred during optimization") }}');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '{{ __("Auto Optimize") }}';
    });
}

// Auto-refresh metrics every 30 seconds
setInterval(function() {
    fetch('{{ route("performance.metrics") }}?type=system')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update memory usage
                const memoryPercent = data.metrics.memory.usage_percent;
                document.querySelector('.progress-bar.bg-primary').style.width = memoryPercent + '%';
                
                // Update disk usage
                const diskPercent = data.metrics.disk.usage_percent;
                document.querySelector('.progress-bar.bg-info').style.width = diskPercent + '%';
            }
        })
        .catch(error => console.error('Error updating metrics:', error));
}, 30000);

// Redis monitoring functions
function refreshRedisData() {
    console.log('Refreshing Redis monitoring data...');

    fetch('{{ route("performance.redis.monitoring") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateRedisDisplay(data.data);
        } else {
            console.error('Failed to get Redis data:', data.error);
        }
    })
    .catch(error => {
        console.error('Error fetching Redis data:', error);
    });
}

function updateRedisDisplay(data) {
    // Update server info
    const serverInfo = data.server_info;
    document.getElementById('redis-status').textContent = serverInfo.status === 'connected' ? 'Connected' : 'Disconnected';
    document.getElementById('redis-status').className = `badge bg-${serverInfo.status === 'connected' ? 'success' : 'danger'}`;

    // Update memory stats
    const memoryStats = data.memory_stats;
    if (memoryStats.used_memory_human) {
        document.getElementById('redis-memory').textContent = memoryStats.used_memory_human;
        document.getElementById('redis-memory-percent').textContent = `${memoryStats.memory_usage_percentage}%`;
    }

    // Update key stats
    const keyStats = data.key_stats;
    document.getElementById('redis-keys').textContent = keyStats.total_keys || 0;

    // Update performance metrics
    const performanceMetrics = data.performance_metrics;
    document.getElementById('redis-hit-ratio').textContent = `${performanceMetrics.cache_hit_ratio}%`;
    document.getElementById('redis-ops-sec').textContent = performanceMetrics.operations_per_second || 0;
    document.getElementById('redis-clients').textContent = performanceMetrics.connected_clients || 0;
    document.getElementById('redis-uptime').textContent = `${performanceMetrics.uptime_hours}h`;

    // Update key distribution
    if (keyStats.keys_by_type) {
        document.getElementById('redis-cache-keys').textContent = keyStats.keys_by_type.cache || 0;
        document.getElementById('redis-session-keys').textContent = keyStats.keys_by_type.session || 0;
        document.getElementById('redis-queue-keys').textContent = keyStats.keys_by_type.queue || 0;
    }
}

function clearRedisCache() {
    if (!confirm('Are you sure you want to clear all Redis cache?')) {
        return;
    }

    fetch('{{ route("performance.redis.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Redis cache cleared successfully!');
            refreshRedisData();
        } else {
            alert('Failed to clear Redis cache: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error clearing Redis cache:', error);
        alert('An error occurred while clearing Redis cache');
    });
}

function warmUpRedisCache() {
    fetch('{{ route("performance.redis.warmup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            refreshRedisData();
        } else {
            alert('Failed to warm up Redis cache: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error warming up Redis cache:', error);
        alert('An error occurred while warming up Redis cache');
    });
}

// Load Redis data on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshRedisData();

    // Auto-refresh Redis data every 30 seconds
    setInterval(refreshRedisData, 30000);
});

// Make functions globally accessible
window.clearCache = clearCache;
window.warmUpCache = warmUpCache;
window.optimizeDatabase = optimizeDatabase;
window.autoOptimize = autoOptimize;
window.refreshRedisData = refreshRedisData;
window.clearRedisCache = clearRedisCache;
window.warmUpRedisCache = warmUpRedisCache;
</script>
@endpush
