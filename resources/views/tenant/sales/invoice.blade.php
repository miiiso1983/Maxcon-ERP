@php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Invoice') }} #{{ $sale->invoice_number }} - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-break { page-break-after: always; }
            body { font-size: 12px; }
            .container { max-width: 100% !important; }
        }
        
        .invoice-header {
            border-bottom: 3px solid #0d6efd;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        
        .company-logo {
            max-height: 80px;
            width: auto;
        }
        
        .invoice-title {
            color: #0d6efd;
            font-weight: bold;
            font-size: 2rem;
        }
        
        .invoice-details {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        
        .table-invoice {
            border: 1px solid #dee2e6;
        }
        
        .table-invoice th {
            background-color: #0d6efd;
            color: white;
            border: none;
        }
        
        .table-invoice td {
            border-bottom: 1px solid #dee2e6;
        }
        
        .total-section {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        
        .payment-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-paid { background-color: #d1e7dd; color: #0f5132; }
        .status-partial { background-color: #fff3cd; color: #664d03; }
        .status-pending { background-color: #cff4fc; color: #055160; }
        .status-overdue { background-color: #f8d7da; color: #721c24; }
        
        .footer-note {
            border-top: 1px solid #dee2e6;
            margin-top: 2rem;
            padding-top: 1rem;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .qr-code-container {
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #0d6efd;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .qr-code-container svg {
            display: block;
            margin: 0 auto;
            border-radius: 0.25rem;
        }

        .qr-code-container small {
            color: #0d6efd;
            font-weight: 600;
        }
        
        /* RTL Support */
        [dir="rtl"] .text-end { text-align: right !important; }
        [dir="rtl"] .text-start { text-align: left !important; }
        [dir="rtl"] .me-auto { margin-left: auto !important; margin-right: 0 !important; }
        [dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Print Controls -->
        <div class="no-print mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ __('Invoice Preview') }}</h4>
                <div>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>{{ __('Print Invoice') }}
                    </button>
                    <a href="{{ route('sales.qr-download', $sale) }}" class="btn btn-outline-info">
                        <i class="fas fa-download me-2"></i>{{ __('Download QR Code') }}
                    </a>
                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('Back to Sale') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="invoice-content">
            <!-- Header -->
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}" class="company-logo me-3" onerror="this.style.display='none'">
                            <div>
                                <h2 class="mb-1">{{ config('app.name', 'MAXCON ERP') }}</h2>
                                <p class="text-muted mb-0">{{ __('Medical & Pharmaceutical Solutions') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="me-3">
                                <h1 class="invoice-title">{{ __('INVOICE') }}</h1>
                                <p class="mb-0"><strong>#{{ $sale->invoice_number }}</strong></p>
                            </div>
                            <div class="qr-code-container">
                                {!! QrCode::size(120)->errorCorrection('M')->generate($compactQrData) !!}
                                <small class="d-block text-center mt-1 text-muted">{{ __('Scan for Details') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="invoice-details">
                        <h5 class="mb-3">{{ __('Bill To') }}:</h5>
                        <strong>{{ $sale->customer->name }}</strong><br>
                        @if($sale->customer->company)
                            {{ $sale->customer->company }}<br>
                        @endif
                        @if($sale->customer->address)
                            {{ $sale->customer->address }}<br>
                        @endif
                        @if($sale->customer->city)
                            {{ $sale->customer->city }}
                            @if($sale->customer->state), {{ $sale->customer->state }}@endif
                            @if($sale->customer->postal_code) {{ $sale->customer->postal_code }}@endif
                            <br>
                        @endif
                        @if($sale->customer->phone)
                            <i class="fas fa-phone me-1"></i>{{ $sale->customer->phone }}<br>
                        @endif
                        @if($sale->customer->email)
                            <i class="fas fa-envelope me-1"></i>{{ $sale->customer->email }}
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="invoice-details">
                        <h5 class="mb-3">{{ __('Invoice Details') }}:</h5>
                        <div class="row">
                            <div class="col-6">
                                <strong>{{ __('Invoice Date') }}:</strong><br>
                                {{ $sale->sale_date->format('M d, Y') }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Due Date') }}:</strong><br>
                                {{ $sale->due_date ? $sale->due_date->format('M d, Y') : __('N/A') }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <strong>{{ __('Payment Method') }}:</strong><br>
                                {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}
                            </div>
                            <div class="col-6">
                                <strong>{{ __('Status') }}:</strong><br>
                                <span class="payment-status status-{{ $sale->payment_status }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </div>
                        </div>
                        @if($sale->reference)
                            <div class="mt-2">
                                <strong>{{ __('Reference') }}:</strong> {{ $sale->reference }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-invoice">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 40%;">{{ __('Product') }}</th>
                            <th style="width: 15%;" class="text-center">{{ __('Quantity') }}</th>
                            <th style="width: 15%;" class="text-end">{{ __('Unit Price') }}</th>
                            <th style="width: 15%;" class="text-end">{{ __('Discount') }}</th>
                            <th style="width: 15%;" class="text-end">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product_name }}</strong><br>
                                <small class="text-muted">{{ __('SKU') }}: {{ $item->product_sku }}</small>
                            </td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }} {{ $sale->currency ?? 'IQD' }}</td>
                            <td class="text-end">{{ number_format($item->discount_amount ?? 0, 2) }} {{ $sale->currency ?? 'IQD' }}</td>
                            <td class="text-end">{{ number_format($item->total_amount, 2) }} {{ $sale->currency ?? 'IQD' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="row">
                <div class="col-md-6">
                    @if($sale->notes)
                    <div class="invoice-details">
                        <h6>{{ __('Notes') }}:</h6>
                        <p class="mb-0">{{ $sale->notes }}</p>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="total-section">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td><strong>{{ __('Subtotal') }}:</strong></td>
                                <td class="text-end">{{ number_format($sale->subtotal, 2) }} {{ $sale->currency ?? 'IQD' }}</td>
                            </tr>
                            @if($sale->discount_amount > 0)
                            <tr>
                                <td><strong>{{ __('Discount') }}:</strong></td>
                                <td class="text-end">-{{ number_format($sale->discount_amount, 2) }} {{ $sale->currency ?? 'IQD' }}</td>
                            </tr>
                            @endif
                            @if($sale->tax_amount > 0)
                            <tr>
                                <td><strong>{{ __('Tax') }}:</strong></td>
                                <td class="text-end">{{ number_format($sale->tax_amount, 2) }} {{ $sale->currency ?? 'IQD' }}</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td><strong>{{ __('Total Amount') }}:</strong></td>
                                <td class="text-end"><strong>{{ number_format($sale->total_amount, 2) }} {{ $sale->currency ?? 'IQD' }}</strong></td>
                            </tr>
                            @if($sale->paid_amount > 0)
                            <tr>
                                <td><strong>{{ __('Paid Amount') }}:</strong></td>
                                <td class="text-end">{{ number_format($sale->paid_amount, 2) }} {{ $sale->currency ?? 'IQD' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Balance Due') }}:</strong></td>
                                <td class="text-end">
                                    <strong class="{{ ($sale->total_amount - $sale->paid_amount) > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($sale->total_amount - $sale->paid_amount, 2) }} {{ $sale->currency ?? 'IQD' }}
                                    </strong>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- QR Code Information -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="invoice-details">
                        <h6 class="mb-2">{{ __('QR Code Information') }}:</h6>
                        <p class="mb-1 small">{{ __('The QR code contains essential invoice data:') }}</p>
                        <ul class="small mb-0">
                            <li>{{ __('Invoice number and customer name') }}</li>
                            <li>{{ __('Sale date and total amount') }}</li>
                            <li>{{ __('Payment status and currency') }}</li>
                            <li>{{ __('Item count and verification URL') }}</li>
                        </ul>
                        <p class="small mt-2 mb-0 text-muted">{{ __('Scan to access full invoice details and verification.') }}</p>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="invoice-details">
                        <h6 class="mb-2">{{ __('Digital Verification') }}:</h6>
                        <p class="small mb-1">{{ __('Scan QR code with any QR reader') }}</p>
                        <p class="small mb-0">{{ __('Or visit:') }}</p>
                        <small class="text-break">{{ route('sales.show', $sale) }}</small>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-note text-center">
                <p class="mb-1"><strong>{{ __('Thank you for your business!') }}</strong></p>
                <p class="mb-0">{{ __('This is a computer-generated invoice and does not require a signature.') }}</p>
                <small>{{ __('Generated on') }} {{ now()->format('M d, Y \a\t H:i') }}</small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
