{{-- resources/views/student/fees/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Fees Dashboard')

@section('content')
<div class="animate-fadeInUp">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Fees</h6>
                    <h2 class="mb-0">₹ {{ number_format($summary['total_fees'], 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Paid</h6>
                    <h2 class="mb-0">₹ {{ number_format($summary['total_paid'], 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Due</h6>
                    <h2 class="mb-0">₹ {{ number_format($summary['total_due'], 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Payment Status</h6>
                    <h2 class="mb-0">{{ $summary['paid_percentage'] }}%</h2>
                    <div class="progress mt-2 bg-light" style="height: 5px;">
                        <div class="progress-bar bg-white" style="width: {{ $summary['paid_percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Fees List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-rupee-sign me-2"></i> Fee Details
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee Category</th>
                                    <th class="text-center">Total Amount</th>
                                    <th class="text-center">Paid</th>
                                    <th class="text-center">Due</th>
                                    <th class="text-center">Due Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fees as $fee)
                                    @php
                                        $dueDate = Carbon\Carbon::parse($fee->due_date);
                                        $isOverdue = $dueDate->isPast() && $fee->due_amount > 0;
                                    @endphp
                                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                        <td>
                                            <strong>{{ $fee->feeStructure->feeCategory->name }}</strong>
                                            @if($fee->feeStructure->frequency != 'one_time')
                                                <br><small class="text-muted">{{ ucfirst($fee->feeStructure->frequency) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">₹ {{ number_format($fee->total_amount, 2) }}</td>
                                        <td class="text-center text-success">₹ {{ number_format($fee->paid_amount, 2) }}</td>
                                        <td class="text-center text-danger">₹ {{ number_format($fee->due_amount, 2) }}</td>
                                        <td class="text-center">
                                            {{ $dueDate->format('d M, Y') }}
                                            @if($isOverdue)
                                                <br><span class="badge bg-danger">Overdue</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($fee->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($fee->status == 'partial')
                                                <span class="badge bg-warning">Partial</span>
                                            @elseif($fee->status == 'overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('student.fees.show', $fee) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-center">₹ {{ number_format($summary['total_fees'], 2) }}</th>
                                    <th class="text-center">₹ {{ number_format($summary['total_paid'], 2) }}</th>
                                    <th class="text-center">₹ {{ number_format($summary['total_due'], 2) }}</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Payments -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> Recent Payments
                    <a href="{{ route('student.payments.history') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($recentPayments->count() > 0)
                        @foreach($recentPayments as $payment)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>₹ {{ number_format($payment->amount, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $payment->payment_date->format('d M, Y') }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                                        <br>
                                        <small>{{ $payment->payment_number }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No payments made yet.</p>
                    @endif
                </div>
            </div>
            
            <!-- Payment Summary -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Payment Summary
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="200"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-success"></i> Paid</span>
                            <span>{{ $summary['paid_count'] }} installments</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span><i class="fas fa-circle text-warning"></i> Partial</span>
                            <span>{{ $summary['pending_count'] }} installments</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span><i class="fas fa-circle text-danger"></i> Overdue</span>
                            <span>{{ $summary['overdue_count'] }} installments</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Paid ({{ $summary["paid_percentage"] }}%)', 'Pending'],
            datasets: [{
                data: [{{ $summary['paid_percentage'] }}, {{ 100 - $summary['paid_percentage'] }}],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush