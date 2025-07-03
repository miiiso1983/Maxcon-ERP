<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Suppliers Performance Report') }} - {{ config('app.name') }}</title>
    
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
        }

        @page {
            margin: 1cm;
            size: A4 landscape;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .report-info {
            font-size: 11px;
            color: #666;
        }
        
        .filters-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .filters-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #0d6efd;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        
        .summary-section {
            background-color: #e3f2fd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #bbdefb;
        }
        
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #1976d2;
            font-size: 14px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            padding: 5px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .summary-label {
            font-weight: bold;
            width: 40%;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #0d6efd;
            font-size: 10px;
            word-wrap: break-word;
        }

        .table td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            font-size: 9px;
            word-wrap: break-word;
            vertical-align: top;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-success {
            color: #28a745;
            font-weight: bold;
        }
        
        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }
        
        .text-warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        /* RTL Support */
        [dir="rtl"] .text-right { text-align: left; }
        [dir="rtl"] .text-left { text-align: right; }
        [dir="rtl"] .table th, [dir="rtl"] .table td { text-align: right; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ config('app.name', 'MAXCON ERP') }}</div>
        <div class="report-title">{{ __('Suppliers Performance Report') }}</div>
        <div class="report-info">
            {{ __('Generated on') }}: {{ now()->format('F d, Y \a\t H:i') }} | 
            {{ __('Total Suppliers') }}: {{ $suppliers->count() }}
        </div>
    </div>

    <!-- Applied Filters -->
    @if(!empty(array_filter($filters)))
    <div class="filters-section">
        <div class="filters-title">{{ __('Applied Filters') }}:</div>
        @if($filters['date_from'])
            <div class="filter-item"><strong>{{ __('From Date') }}:</strong> {{ $filters['date_from'] }}</div>
        @endif
        @if($filters['date_to'])
            <div class="filter-item"><strong>{{ __('To Date') }}:</strong> {{ $filters['date_to'] }}</div>
        @endif
        @if($filters['supplier_type'])
            <div class="filter-item"><strong>{{ __('Type') }}:</strong> {{ ucfirst($filters['supplier_type']) }}</div>
        @endif
        @if($filters['status'])
            <div class="filter-item"><strong>{{ __('Status') }}:</strong> {{ ucfirst($filters['status']) }}</div>
        @endif
    </div>
    @endif

    <!-- Summary Statistics -->
    <div class="summary-section">
        <div class="summary-title">{{ __('Report Summary') }}</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell summary-label">{{ __('Total Suppliers') }}:</div>
                <div class="summary-cell">{{ $suppliers->count() }}</div>
                <div class="summary-cell summary-label">{{ __('Active Suppliers') }}:</div>
                <div class="summary-cell">{{ $suppliers->where('is_active', true)->count() }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">{{ __('Inactive Suppliers') }}:</div>
                <div class="summary-cell">{{ $suppliers->where('is_active', false)->count() }}</div>
                <div class="summary-cell summary-label">{{ __('Total Orders') }}:</div>
                <div class="summary-cell">{{ number_format($suppliers->sum('total_orders')) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">{{ __('Total Spent') }}:</div>
                <div class="summary-cell">{{ number_format($suppliers->sum('total_spent'), 2) }} {{ $currency ?? 'IQD' }}</div>
                <div class="summary-cell summary-label">{{ __('Average Rating') }}:</div>
                <div class="summary-cell">{{ number_format($suppliers->avg('rating'), 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-label">{{ __('Total Credit Limit') }}:</div>
                <div class="summary-cell">{{ number_format($suppliers->sum('credit_limit'), 2) }} {{ $currency ?? 'IQD' }}</div>
                <div class="summary-cell summary-label">{{ __('Report Type') }}:</div>
                <div class="summary-cell">{{ __('Performance Analysis') }}</div>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    @if($suppliers->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">{{ __('Code') }}</th>
                <th style="width: 20%;">{{ __('Supplier Name') }}</th>
                <th style="width: 15%;">{{ __('Contact') }}</th>
                <th style="width: 10%;">{{ __('Type') }}</th>
                <th style="width: 8%;">{{ __('Orders') }}</th>
                <th style="width: 12%;">{{ __('Total Spent') }}</th>
                <th style="width: 8%;">{{ __('Rating') }}</th>
                <th style="width: 10%;">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $index => $supplier)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $supplier['supplier_code'] ?? 'N/A' }}</td>
                <td><strong>{{ $supplier['name'] ?? 'Unknown' }}</strong></td>
                <td>
                    {{ $supplier['contact_person'] ?? 'N/A' }}<br>
                    <small>{{ $supplier['phone'] ?? 'N/A' }}</small>
                </td>
                <td>{{ $supplier['supplier_type'] ?? 'N/A' }}</td>
                <td class="text-center">{{ number_format($supplier['total_orders'] ?? 0) }}</td>
                <td class="text-right">{{ number_format($supplier['total_spent'] ?? 0, 2) }}</td>
                <td class="text-center">
                    @php $rating = $supplier['rating'] ?? 0; @endphp
                    @if($rating >= 4.0)
                        <span class="text-success">{{ number_format($rating, 1) }}</span>
                    @elseif($rating >= 3.0)
                        <span class="text-warning">{{ number_format($rating, 1) }}</span>
                    @else
                        <span class="text-danger">{{ number_format($rating, 1) }}</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($supplier['is_active'] ?? false)
                        <span class="badge badge-success">{{ __('Active') }}</span>
                    @else
                        <span class="badge badge-danger">{{ __('Inactive') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
        <h3 style="color: #6c757d;">{{ __('No Suppliers Found') }}</h3>
        <p style="color: #6c757d;">{{ __('No suppliers match the selected criteria.') }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>{{ config('app.name') }}</strong> - {{ __('Suppliers Performance Report') }}</p>
        <p>{{ __('This report contains confidential business information') }}</p>
        <p>{{ __('Generated on') }} {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>
</body>
</html>
