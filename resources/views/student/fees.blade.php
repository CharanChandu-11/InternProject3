{{-- resources/views/student/fees.blade.php --}}
@extends('layouts.student')

@section('title', 'Fees')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-rupee-sign me-2"></i> Fee Details
            </div>
            <div class="card-body">
                @if($fees->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fees as $fee)
                                    <tr>
                                        <td>{{ $fee->feeStructure->feeCategory->name }}</td>
                                        <td>₹{{ number_format($fee->total_amount, 2) }}</td>
                                        <td>₹{{ number_format($fee->paid_amount, 2) }}</td>
                                        <td class="{{ $fee->due_amount > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                            ₹{{ number_format($fee->due_amount, 2) }}
                                        </td>
                                        <td>{{ $fee->due_date->format('d M, Y') }}</td>
                                        <td>
                                            @if($fee->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($fee->status == 'partial')
                                                <span class="badge bg-warning">Partial</span>
                                            @elseif($fee->due_date->isPast())
                                                <span class="badge bg-danger">Overdue</span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th>₹{{ number_format($totalFees, 2) }}</th>
                                    <th>₹{{ number_format($totalPaid, 2) }}</th>
                                    <th>₹{{ number_format($totalDue, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No fee records found.</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Payment History
            </div>
            <div class="card-body">
                @if($payments->count() > 0)
                    @foreach($payments as $payment)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <span>₹{{ number_format($payment->amount, 2) }}</span>
                                <span class="text-muted small">{{ $payment->payment_date->format('d M, Y') }}</span>
                            </div>
                            <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
                            <div class="small">Receipt No: {{ $payment->payment_number }}</div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">No payment history.</p>
                @endif
            </div>
        </div>
        
        @if($totalDue > 0)
            <div class="card mt-3">
                <div class="card-body text-center">
                    <h5>Total Due: ₹{{ number_format($totalDue, 2) }}</h5>
                    <form action="{{ route('student.fees.pay') }}" method="POST">
                        @csrf
                        <input type="hidden" name="amount" value="{{ $totalDue }}">
                        <button type="submit" class="btn btn-primary mt-2">Pay Now</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection