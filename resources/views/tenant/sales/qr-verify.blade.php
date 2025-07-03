@extends('tenant.layouts.app')

@section('title', __('Invoice Verification'))
@section('page-title', __('Invoice Verification'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">{{ __('app.dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">{{ __('app.sales') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Invoice Verification') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Verification Header -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-success mb-2">
                                <i class="fas fa-check-circle me-2"></i>{{ __('Invoice Verified Successfully') }}
                            </h3>
                            <p class="text-muted mb-0">{{ __('This invoice has been verified as authentic and contains the following information:') }}</p>
                        </div>
                        <div class="col-md-4">
                            <div class="verification-badge">
                                <i class="fas fa-qrcode fa-3x text-primary mb-2"></i>
                                <p class="small mb-0">{{ __('QR Code Verified') }}</p>
                                <small class="text-muted">{{ now()->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Summary -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-invoice me-2"></i>{{ __('Invoice Details') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Invoice Number') }}:</strong></td>
                                    <td>{{ $qrData['invoice_number'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Sale Date') }}:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($qrData['sale_date'])->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Due Date') }}:</strong></td>
                                    <td>{{ $qrData['due_date'] ? \Carbon\Carbon::parse($qrData['due_date'])->format('M d, Y') : __('N/A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Payment Method') }}:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $qrData['payment_method'])) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Payment Status') }}:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $qrData['payment_status'] === 'paid' ? 'success' : ($qrData['payment_status'] === 'partial' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($qrData['payment_status']) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>{{ __('Customer Information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Name') }}:</strong></td>
                                    <td>{{ $qrData['customer']['name'] }}</td>
                                </tr>
                                @if($qrData['customer']['phone'])
                                <tr>
                                    <td><strong>{{ __('Phone') }}:</strong></td>
                                    <td>{{ $qrData['customer']['phone'] }}</td>
                                </tr>
                                @endif
                                @if($qrData['customer']['email'])
                                <tr>
                                    <td><strong>{{ __('Email') }}:</strong></td>
                                    <td>{{ $qrData['customer']['email'] }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator me-2"></i>{{ __('Financial Summary') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Subtotal') }}:</strong></td>
                                    <td class="text-end">{{ number_format($qrData['totals']['subtotal'], 2) }} {{ $qrData['currency'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Discount') }}:</strong></td>
                                    <td class="text-end">{{ number_format($qrData['totals']['discount'], 2) }} {{ $qrData['currency'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Tax') }}:</strong></td>
                                    <td class="text-end">{{ number_format($qrData['totals']['tax'], 2) }} {{ $qrData['currency'] }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr class="table-primary">
                                    <td><strong>{{ __('Total Amount') }}:</strong></td>
                                    <td class="text-end"><strong>{{ number_format($qrData['totals']['total'], 2) }} {{ $qrData['currency'] }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Paid Amount') }}:</strong></td>
                                    <td class="text-end">{{ number_format($qrData['totals']['paid'], 2) }} {{ $qrData['currency'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Balance Due') }}:</strong></td>
                                    <td class="text-end">
                                        <strong class="{{ $qrData['totals']['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($qrData['totals']['balance'], 2) }} {{ $qrData['currency'] }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>{{ __('Invoice Items') }} ({{ count($qrData['items']) }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th class="text-center">{{ __('Quantity') }}</th>
                                    <th class="text-end">{{ __('Unit Price') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($qrData['items'] as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item['name'] }}</td>
                                    <td><code>{{ $item['sku'] }}</code></td>
                                    <td class="text-center">{{ number_format($item['quantity'], 2) }}</td>
                                    <td class="text-end">{{ number_format($item['unit_price'], 2) }} {{ $qrData['currency'] }}</td>
                                    <td class="text-end">{{ number_format($item['total'], 2) }} {{ $qrData['currency'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <h6 class="mb-3">{{ __('Additional Actions') }}</h6>
                    <div class="btn-group" role="group">
                        <a href="{{ $qrData['invoice_url'] }}" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>{{ __('View Full Invoice') }}
                        </a>
                        <a href="{{ route('sales.print', $sale) }}" class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-print me-2"></i>{{ __('Print Invoice') }}
                        </a>
                        <a href="{{ route('sales.qr-download', $sale) }}" class="btn btn-outline-info">
                            <i class="fas fa-download me-2"></i>{{ __('Download QR Code') }}
                        </a>
                        <button class="btn btn-outline-success" onclick="copyVerificationData()">
                            <i class="fas fa-copy me-2"></i>{{ __('Copy Data') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Verification Footer -->
            <div class="text-center mt-4 mb-4">
                <small class="text-muted">
                    {{ __('This verification was generated on') }} {{ \Carbon\Carbon::parse($qrData['generated_at'])->format('M d, Y \a\t H:i') }}<br>
                    {{ __('Invoice authenticity guaranteed by') }} <strong>{{ config('app.name') }}</strong>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for copying -->
<textarea id="verification-data" style="position: absolute; left: -9999px;">{{ json_encode($qrData, JSON_PRETTY_PRINT) }}</textarea>
@endsection

@push('scripts')
<script>
function copyVerificationData() {
    const textarea = document.getElementById('verification-data');
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        alert('{{ __("Verification data copied to clipboard!") }}');
    } catch (err) {
        alert('{{ __("Failed to copy data. Please try again.") }}');
    }
}
</script>
@endpush
