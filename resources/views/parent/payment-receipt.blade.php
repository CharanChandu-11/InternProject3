{{-- resources/views/parent/payment-receipt.blade.php --}}
@extends('layouts.parent')

@section('title', 'Payment Receipt')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">Payment Receipt</h4>
                    <p class="text-muted mb-0">Receipt No: {{ $payment->payment_number }}</p>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3>{{ $payment->student->class->schoolSetting->school_name ?? 'Smart School' }}</h3>
                        <p class="text-muted">{{ $payment->student->class->schoolSetting->address ?? '' }}</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <strong>Student Name:</strong> {{ $payment->student->user->name }}<br>
                            <strong>Admission No:</strong> {{ $payment->student->admission_number }}<br>
                            <strong>Class:</strong> {{ $payment->student->class->name }} - {{ $payment->student->section->name }}
                        </div>
                        <div class="col-6 text-end">
                            <strong>Payment Date:</strong> {{ $payment->payment_date->format('d M, Y') }}<br>
                            <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}<br>
                            <strong>Transaction ID:</strong> {{ $payment->transaction_id ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Particulars</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $payment->studentFee->feeStructure->feeCategory->name }}</td>
                                <td class="text-end">₹{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">₹{{ number_format($payment->amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">Thank you for your payment!</p>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print me-2"></i> Print Receipt
                        </button>
                        <a href="{{ route('parent.children.fees', $payment->student) }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-arrow-left me-2"></i> Back to Fees
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .sidebar, .topbar, .btn, .card-header .btn, .alert, .navbar {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        body {
            background: white !important;
        }
    }
</style>
@endpush