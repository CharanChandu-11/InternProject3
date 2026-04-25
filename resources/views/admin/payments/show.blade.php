{{-- resources/views/admin/payments/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Payment Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-receipt me-2"></i> Payment Details
            <div class="float-end">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <strong>Payment Information</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Receipt Number:</th>
                                    <td><strong>#{{ $payment->payment_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td><span class="badge bg-success fs-6">₹ {{ number_format($payment->amount, 2) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                </tr>
                                <tr>
                                    <th>Transaction ID:</th>
                                    <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Date:</th>
                                    <td>{{ $payment->payment_date->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($payment->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($payment->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($payment->status == 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Received By:</th>
                                    <td>{{ $payment->receivedBy->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <th>Remarks:</th>
                                    <td>{{ $payment->remarks ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <strong>Student Information</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Student Name:</th>
                                    <td>{{ $payment->student->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Admission Number:</th>
                                    <td>{{ $payment->student->admission_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Class:</th>
                                    <td>{{ $payment->student->class->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Section:</th>
                                    <td>{{ $payment->student->section->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Roll Number:</th>
                                    <td>{{ $payment->student->roll_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($payment->studentFee)
                    <div class="card bg-light">
                        <div class="card-header">
                            <strong>Fee Details</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Fee Category:</th>
                                    <td>{{ $payment->studentFee->feeStructure->feeCategory->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td>₹ {{ number_format($payment->studentFee->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Paid Amount:</th>
                                    <td>₹ {{ number_format($payment->studentFee->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Due Amount:</th>
                                    <td>₹ {{ number_format($payment->studentFee->due_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td>{{ $payment->studentFee->due_date->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($payment->studentFee->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($payment->studentFee->status == 'partial')
                                            <span class="badge bg-warning">Partial</span>
                                        @elseif($payment->studentFee->status == 'pending')
                                            <span class="badge bg-danger">Pending</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($payment->studentFee->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection