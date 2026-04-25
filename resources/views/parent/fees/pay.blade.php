{{-- resources/views/parent/fees/pay.blade.php --}}
@extends('layouts.parent')

@section('title', 'Pay Fee')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-credit-card me-2"></i> Pay Fee: {{ $fee->feeStructure->feeCategory->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.fees', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Fee Details</h6>
                            <hr>
                            <p><strong>Student:</strong> {{ $student->user->name }}</p>
                            <p><strong>Fee Category:</strong> {{ $fee->feeStructure->feeCategory->name }}</p>
                            <p><strong>Total Amount:</strong> ₹ {{ number_format($fee->total_amount, 2) }}</p>
                            <p><strong>Already Paid:</strong> ₹ {{ number_format($fee->paid_amount, 2) }}</p>
                            <p><strong>Due Amount:</strong> ₹ {{ number_format($fee->due_amount, 2) }}</p>
                            <p><strong>Due Date:</strong> {{ $fee->due_date->format('d-m-Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6>Payment Information</h6>
                            <hr>
                            <form action="{{ route('parent.fees.pay.process', $fee) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Amount to Pay <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" name="amount" class="form-control" 
                                               min="1" max="{{ $fee->due_amount }}" 
                                               value="{{ $fee->due_amount }}" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="card">Credit/Debit Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="online">Online Payment</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Transaction ID (Optional)</label>
                                    <input type="text" name="transaction_id" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-credit-card me-1"></i> Pay Now
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection