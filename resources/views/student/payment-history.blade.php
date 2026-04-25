{{-- resources/views/student/payment-history.blade.php --}}
@extends('layouts.student')

@section('title', 'Payment History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Payment History
                <a href="{{ route('student.fees') }}" class="btn btn-sm btn-secondary float-end">Back to Fees</a>
            </div>
            <div class="card-body">
                @if($payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                 <tr>
                                    <th>Receipt No</th>
                                    <th>Date</th>
                                    <th>Fee Category</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Received By</th>
                                    <th>Receipt</th>
                                 </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                     <tr>
                                        <td>{{ $payment->payment_number }}</td>
                                        <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                        <td>{{ $payment->studentFee->feeStructure->feeCategory->name }}</td>
                                        <td class="fw-bold">₹{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                        <td>{{ $payment->receivedBy->name ?? 'System' }}</td>
                                        <td>
                                            <a href="{{ route('download.receipt', $payment) }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-download"></i> Receipt
                                            </a>
                                        </td>
                                     </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $payments->links() }}
                @else
                    <p class="text-muted text-center py-4">No payment history found.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection