{{-- resources/views/student/fees/history.blade.php --}}
@extends('layouts.student')

@section('title', 'Payment History')

@section('content')
<div class="animate-fadeInUp">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Payments</h6>
                    <h2 class="mb-0">₹ {{ number_format($summary['total_paid'], 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Transactions</h6>
                    <h2 class="mb-0">{{ $summary['total_transactions'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">This Month</h6>
                    <h2 class="mb-0">₹ {{ number_format($summary['this_month'], 2) }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history me-2"></i> Payment History
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('student.payments.history') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Payments Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt No</th>
                            <th>Fee Category</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Transaction ID</th>
                            <th>Payment Date</th>
                            <th>Received By</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->studentFee->feeStructure->feeCategory->name }}</td>
                            <td><span class="fw-bold">₹ {{ number_format($payment->amount, 2) }}</span></td>
                            <td>{{ ucfirst($payment->payment_method) }}</span></td>
                            <td>{{ $payment->transaction_id ?? '-' }}</span></td>
                            <td>{{ $payment->payment_date->format('d M, Y h:i A') }}</span></td>
                            <td>{{ $payment->receivedBy->name ?? 'System' }}</span></td>
                            <td>
                                <a href="{{ route('student.fees.download-receipt', $payment) }}" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-download"></i> Receipt
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection