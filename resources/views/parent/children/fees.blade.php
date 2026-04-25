{{-- resources/views/parent/children/fees.blade.php --}}
@extends('layouts.parent')

@section('title', 'Fees - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-rupee-sign me-2"></i> Fee Details: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Fees</h6>
                            <h3>₹ {{ number_format($summary['total_fees'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Total Paid</h6>
                            <h3>₹ {{ number_format($summary['total_paid'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6>Total Due</h6>
                            <h3>₹ {{ number_format($summary['total_due'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Fees Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fee Category</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fees as $fee)
                        <tr>
                            <td>{{ $fee->feeStructure->feeCategory->name }}</td>
                            <td>₹ {{ number_format($fee->total_amount, 2) }}</td>
                            <td>₹ {{ number_format($fee->paid_amount, 2) }}</span></td>
                            <td><strong class="text-danger">₹ {{ number_format($fee->due_amount, 2) }}</strong></span></td>
                            <td>{{ $fee->due_date->format('d-m-Y') }}</span></td>
                            <td>
                                @if($fee->status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($fee->status == 'partial')
                                    <span class="badge bg-warning">Partial</span>
                                @elseif($fee->status == 'overdue')
                                    <span class="badge bg-danger">Overdue</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                             </span></td>
                            <td>
                                @if($fee->due_amount > 0)
                                    <a href="{{ route('parent.fees.pay.form', $fee) }}" class="btn btn-sm btn-primary">
                                        Pay Now
                                    </a>
                                @endif
                             </span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection