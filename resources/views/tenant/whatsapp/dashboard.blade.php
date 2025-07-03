@extends('tenant.layouts.app')

@section('title', __('WhatsApp Integration'))
@section('page-title', __('WhatsApp Business Dashboard'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('WhatsApp') }}</li>
@endsection

@section('content')
<!-- WhatsApp Overview Metrics -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Total Messages') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['total_messages'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fab fa-whatsapp fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Today') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['messages_today'] ?? 0 }}</div>
                        <small class="text-muted">{{ __('messages sent') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Delivery Rate') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ number_format($overview['delivery_rate'] ?? 0, 1) }}%</div>
                        <small class="text-muted">{{ __('successful delivery') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Pending') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['pending_messages'] ?? 0 }}</div>
                        <small class="text-muted">{{ __('in queue') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-danger">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Failed') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['failed_messages'] ?? 0 }}</div>
                        <small class="text-muted">{{ __('delivery failed') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
        <div class="card stats-card-secondary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col {{ marginEnd('2') }}">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            {{ __('Templates') }}
                        </div>
                        <div class="h5 mb-0 font-weight-bold">{{ $overview['active_templates'] ?? 0 }}</div>
                        <small class="text-muted">{{ __('active templates') }}</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x opacity-75"></i>
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
                <h6 class="m-0">{{ __('Quick Actions') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="fab fa-whatsapp fa-3x text-success mb-3"></i>
                                <h6>{{ __('Send Message') }}</h6>
                                <p class="text-muted small">{{ __('Send WhatsApp message to customer') }}</p>
                                <a href="{{ route('whatsapp.messages.create') }}" class="btn btn-success btn-sm">
                                    {{ __('Send Message') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i>
                                <h6>{{ __('Send Invoice') }}</h6>
                                <p class="text-muted small">{{ __('Send invoice via WhatsApp') }}</p>
                                <button class="btn btn-primary btn-sm" onclick="showInvoiceModal()">
                                    {{ __('Send Invoice') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-bell fa-3x text-warning mb-3"></i>
                                <h6>{{ __('Payment Reminder') }}</h6>
                                <p class="text-muted small">{{ __('Send payment reminder') }}</p>
                                <button class="btn btn-warning btn-sm" onclick="showReminderModal()">
                                    {{ __('Send Reminder') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-info mb-3"></i>
                                <h6>{{ __('Bulk Message') }}</h6>
                                <p class="text-muted small">{{ __('Send message to multiple customers') }}</p>
                                <button class="btn btn-info btn-sm" onclick="showBulkModal()">
                                    {{ __('Bulk Send') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Statistics & Message Trends -->
<div class="row mb-4">
    <!-- Delivery Statistics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Delivery Statistics (Last 30 Days)') }}</h6>
            </div>
            <div class="card-body">
                @if(!empty($deliveryStats))
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $deliveryStats['sent'] ?? 0 }}</h4>
                            <small class="text-muted">{{ __('Sent') }}</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success">{{ $deliveryStats['delivered'] ?? 0 }}</h4>
                        <small class="text-muted">{{ __('Delivered') }}</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-info">{{ $deliveryStats['read'] ?? 0 }}</h4>
                            <small class="text-muted">{{ __('Read') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $deliveryStats['failed'] ?? 0 }}</h4>
                        <small class="text-muted">{{ __('Failed') }}</small>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ __('Delivery Rate') }}</span>
                        <span class="text-success">{{ number_format($deliveryStats['delivery_rate'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $deliveryStats['delivery_rate'] ?? 0 }}%"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ __('Read Rate') }}</span>
                        <span class="text-info">{{ number_format($deliveryStats['read_rate'] ?? 0, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ $deliveryStats['read_rate'] ?? 0 }}%"></div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fab fa-whatsapp fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No delivery statistics available') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Message Trends Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0">{{ __('Message Trends (Last 7 Days)') }}</h6>
            </div>
            <div class="card-body">
                @if(!empty($trends) && count($trends) > 0)
                <canvas id="messageTrendsChart" height="200"></canvas>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No trend data available') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Messages & Template Usage -->
<div class="row">
    <!-- Recent Messages -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Recent Messages') }}</h6>
                <a href="{{ route('whatsapp.messages.index') }}" class="btn btn-outline-primary btn-sm">
                    {{ __('View All') }}
                </a>
            </div>
            <div class="card-body p-0">
                @if(!empty($recentMessages) && count($recentMessages) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Recipient') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Sent At') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMessages as $message)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $message->recipient_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $message->formatted_phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $message->message_type)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $message->status_color }}">
                                        {{ ucfirst($message->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($message->sent_at)
                                        {{ is_string($message->sent_at) ? $message->sent_at : $message->sent_at->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">{{ __('Not sent') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('whatsapp.messages.show', $message) }}" class="btn btn-outline-primary btn-sm">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fab fa-whatsapp fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No recent messages') }}</p>
                    <a href="{{ route('whatsapp.messages.create') }}" class="btn btn-primary">
                        {{ __('Send Your First Message') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Template Usage -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0">{{ __('Popular Templates') }}</h6>
                <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-outline-primary btn-sm">
                    {{ __('Manage') }}
                </a>
            </div>
            <div class="card-body p-0">
                @if(!empty($templateUsage) && count($templateUsage) > 0)
                <div class="list-group list-group-flush">
                    @foreach($templateUsage as $template)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $template['name'] }}</strong>
                                <br>
                                <small class="text-muted">
                                    @if($template['last_used'])
                                        {{ __('Last used') }}: {{ is_string($template['last_used']) ? $template['last_used'] : $template['last_used']->format('M d, Y') }}
                                    @else
                                        {{ __('Never used') }}
                                    @endif
                                </small>
                            </div>
                            <span class="badge bg-primary">{{ $template['usage_count'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted">{{ __('No templates available') }}</p>
                    <a href="{{ route('whatsapp.templates.create') }}" class="btn btn-primary btn-sm">
                        {{ __('Create Template') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(!empty($trends) && count($trends) > 0)
// Message Trends Chart
const trendsCtx = document.getElementById('messageTrendsChart').getContext('2d');
const trendsChart = new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($trends, 'date')) !!},
        datasets: [
            {
                label: '{{ __("Sent") }}',
                data: {!! json_encode(array_column($trends, 'sent')) !!},
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            },
            {
                label: '{{ __("Delivered") }}',
                data: {!! json_encode(array_column($trends, 'delivered')) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            },
            {
                label: '{{ __("Failed") }}',
                data: {!! json_encode(array_column($trends, 'failed')) !!},
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
};
@endif

// Quick action functions
function showInvoiceModal() {
    // Implementation for invoice modal
    alert('{{ __("Invoice sending feature will be implemented") }}');
}

function showReminderModal() {
    // Implementation for reminder modal
    alert('{{ __("Payment reminder feature will be implemented") }}');
}

function showBulkModal() {
    // Implementation for bulk message modal
    alert('{{ __("Bulk messaging feature will be implemented") }}');
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush
