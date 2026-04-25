{{-- resources/views/parent/fees/history.blade.php --}}
@extends('layouts.parent')

@section('title', 'Payment History')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history me-2"></i> Payment History
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="student_id" class="form-select">
                            <option value="">All Children</option>
                            @foreach($students as $child)
                                <option value="{{ $child->id }}" {{ request('student_id') == $child->id ? 'selected' : '' }}>
                                    {{ $child->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('parent.payments.history') }}" class="btn btn-secondary btn-sm">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Receipt No</th>
                            <th>Student</th>
                            <th>Fee Category</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</span></td>
                            <td>{{ $payment->student->user->name }}</span></td>
                            <td>{{ $payment->studentFee->feeStructure->feeCategory->name }}</span></td>
                            <td>₹ {{ number_format($payment->amount, 2) }}</span></td>
                            <td>{{ ucfirst($payment->payment_method) }}</span></td>
                            <td>{{ $payment->payment_date->format('d-m-Y') }}</span></td>
                            <td>
                                <a href="{{ route('parent.payments.receipt', $payment) }}" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-download"></i> Receipt
                                </a>
                             </span></td>
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