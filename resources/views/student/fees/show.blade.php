{{-- resources/views/student/fees/show.blade.php --}}
@extends('layouts.student')

@section('title', 'Fee Details - ' . $fee->feeStructure->feeCategory->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-receipt me-2"></i> Fee Details: {{ $fee->feeStructure->feeCategory->name }}
            <div class="float-end">
                <a href="{{ route('student.fees') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Fees
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Fee Information -->
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Fee Information</h6>
                            <hr>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Fee Category:</th>
                                    <td>{{ $fee->feeStructure->feeCategory->name }}</td>
                                </tr>
                                <tr>
                                    <th>Frequency:</th>
                                    <td>{{ ucfirst($fee->feeStructure->frequency) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td class="fw-bold">₹ {{ number_format($fee->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Paid Amount:</th>
                                    <td class="text-success fw-bold">₹ {{ number_format($fee->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Due Amount:</th>
                                    <td class="text-danger fw-bold">₹ {{ number_format($fee->due_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td>
                                        {{ Carbon\Carbon::parse($fee->due_date)->format('l, F j, Y') }}
                                        @if(Carbon\Carbon::parse($fee->due_date)->isPast() && $fee->due_amount > 0)
                                            <span class="badge bg-danger ms-2">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($fee->status == 'paid')
                                            <span class="badge bg-success">Fully Paid</span>
                                        @elseif($fee->status == 'partial')
                                            <span class="badge bg-warning">Partially Paid</span>
                                        @elseif($fee->status == 'overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Form -->
                <div class="col-md-6">
                    @if($canPay)
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-credit-card me-2"></i> Make Payment
                            </div>
                            <div class="card-body">
                                <form action="{{ route('student.fees.pay', $fee) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Amount to Pay <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" name="amount" class="form-control" 
                                                   min="1" max="{{ $fee->due_amount }}" 
                                                   value="{{ $fee->due_amount }}" required>
                                        </div>
                                        <small class="text-muted">Maximum: ₹ {{ number_format($fee->due_amount, 2) }}</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" class="form-select" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="card">Credit/Debit Card</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="online">Online Payment</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Transaction ID (Optional)</label>
                                        <input type="text" name="transaction_id" class="form-control" 
                                               placeholder="Enter transaction reference">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-credit-card me-1"></i> Pay Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            This fee has been fully paid. Thank you!
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Payment History -->
            @if($payments->count() > 0)
                <div class="mt-4">
                    <h5 class="mb-3">Payment History</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt No</th>
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
                                    <td>₹ {{ number_format($payment->amount, 2) }}</span></td>
                                    <td>{{ ucfirst($payment->payment_method) }}</span></td>
                                    <td>{{ $payment->transaction_id ?? '-' }}</span></td>
                                    <td>{{ $payment->payment_date->format('d M, Y h:i A') }}</span></td>
                                    <td>{{ $payment->receivedBy->name ?? 'System' }}</span></td>
                                    <td>
                                        <a href="{{ route('student.fees.download-receipt', $payment) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection