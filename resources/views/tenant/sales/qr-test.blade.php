@extends('tenant.layouts.app')

@section('title', __('QR Code Test'))
@section('page-title', __('QR Code Test'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-qrcode me-2"></i>{{ __('QR Code Data Test') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ __('Sample QR Data') }}:</h6>
                            <textarea id="qr-data" class="form-control" rows="8" readonly>{"inv":"INV-20250702-0002","cust":"Ahmed Ali Hassan","date":"2025-06-03","total":16.42,"curr":"IQD","status":"pending","items":2,"verify":"http://localhost:8000/sales/2/qr-verify"}</textarea>
                            
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="decodeQRData()">
                                    <i class="fas fa-search me-2"></i>{{ __('Decode QR Data') }}
                                </button>
                                <button class="btn btn-outline-secondary" onclick="copyQRData()">
                                    <i class="fas fa-copy me-2"></i>{{ __('Copy Data') }}
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>{{ __('Decoded Result') }}:</h6>
                            <div id="decoded-result" class="border rounded p-3" style="min-height: 200px; background-color: #f8f9fa;">
                                <em class="text-muted">{{ __('Click "Decode QR Data" to see the result') }}</em>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>{{ __('QR Code Information') }}:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Field') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Example') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>inv</code></td>
                                            <td>{{ __('Invoice Number') }}</td>
                                            <td>INV-20250702-0002</td>
                                        </tr>
                                        <tr>
                                            <td><code>cust</code></td>
                                            <td>{{ __('Customer Name') }}</td>
                                            <td>Ahmed Ali Hassan</td>
                                        </tr>
                                        <tr>
                                            <td><code>date</code></td>
                                            <td>{{ __('Sale Date') }}</td>
                                            <td>2025-06-03</td>
                                        </tr>
                                        <tr>
                                            <td><code>total</code></td>
                                            <td>{{ __('Total Amount') }}</td>
                                            <td>16.42</td>
                                        </tr>
                                        <tr>
                                            <td><code>curr</code></td>
                                            <td>{{ __('Currency') }}</td>
                                            <td>IQD</td>
                                        </tr>
                                        <tr>
                                            <td><code>status</code></td>
                                            <td>{{ __('Payment Status') }}</td>
                                            <td>pending</td>
                                        </tr>
                                        <tr>
                                            <td><code>items</code></td>
                                            <td>{{ __('Number of Items') }}</td>
                                            <td>2</td>
                                        </tr>
                                        <tr>
                                            <td><code>verify</code></td>
                                            <td>{{ __('Verification URL') }}</td>
                                            <td>http://localhost:8000/sales/2/qr-verify</td>
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
</div>
@endsection

@push('scripts')
<script>
function decodeQRData() {
    const qrData = document.getElementById('qr-data').value;
    const resultDiv = document.getElementById('decoded-result');
    
    try {
        const decoded = JSON.parse(qrData);
        
        let html = '<h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Successfully Decoded</h6>';
        html += '<table class="table table-sm table-borderless">';
        
        for (const [key, value] of Object.entries(decoded)) {
            html += `<tr><td><strong>${key}:</strong></td><td>${value}</td></tr>`;
        }
        
        html += '</table>';
        html += `<div class="mt-3">`;
        html += `<a href="${decoded.verify}" class="btn btn-sm btn-primary" target="_blank">`;
        html += `<i class="fas fa-external-link-alt me-1"></i>Open Verification Page</a>`;
        html += `</div>`;
        
        resultDiv.innerHTML = html;
        
    } catch (error) {
        resultDiv.innerHTML = `
            <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Decode Error</h6>
            <p class="text-danger">${error.message}</p>
        `;
    }
}

function copyQRData() {
    const textarea = document.getElementById('qr-data');
    textarea.select();
    textarea.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        alert('{{ __("QR data copied to clipboard!") }}');
    } catch (err) {
        alert('{{ __("Failed to copy data. Please try again.") }}');
    }
}
</script>
@endpush
