{{-- resources/views/admin/reports/fees.blade.php --}}
@extends('layouts.admin')

@section('title', 'Fees Collection Report')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-rupee-sign me-2"></i> Fees Collection Report
            <div class="float-end">
                <button class="btn btn-sm btn-success" id="exportExcel">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
                <button class="btn btn-sm btn-danger" id="exportPDF">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" id="sectionFilter">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('admin.reports.fees') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Collected</h6>
                            <h3>{{ $summary['total_collected_formatted'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Transactions</h6>
                            <h3>{{ number_format($summary['total_transactions']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Outstanding Fees</h6>
                            <h3>{{ $outstandingSummary['total_outstanding_formatted'] }}</h3>
                            <small>{{ $outstandingSummary['total_students'] }} students</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collection by Payment Method -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-pie me-2"></i> Collection by Payment Method
                        </div>
                        <div class="card-body">
                            <canvas id="paymentMethodChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-line me-2"></i> Collection Trend
                        </div>
                        <div class="card-body">
                            <canvas id="collectionTrendChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collection Trend Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Daily Collection Trend
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Transactions</th>
                                    <th>Amount Collected</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collectionTrend as $trend)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($trend['date'])->format('d-m-Y') }}</span></td>
                                        <td>{{ $trend['day'] }}</span></td>
                                        <td>{{ number_format($trend['count']) }}</span></td>
                                        <td><span class="fw-bold text-success">{{ $trend['amount_formatted'] }}</span></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Records -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> Payment Records
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Receipt No</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Fee Category</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Date</th>
                                    <th>Received By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_number }}</span></td>
                                    <td>
                                        {{ $payment->student->user->name }}<br>
                                        <small class="text-muted">{{ $payment->student->admission_number }}</small>
                                     </span></td>
                                    <td>{{ $payment->student->class->name ?? 'N/A' }}</span></td>
                                    <td>{{ $payment->studentFee->feeStructure->feeCategory->name ?? 'N/A' }}</span></td>
                                    <td><span class="fw-bold">₹ {{ number_format($payment->amount, 2) }}</span></span></td>
                                    <td>{{ ucfirst($payment->payment_method) }}</span></td>
                                    <td>{{ $payment->payment_date->format('d-m-Y') }}</span></td>
                                    <td>{{ $payment->receivedBy->name ?? 'System' }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $payments->links() }}
                </div>
            </div>

            <!-- Outstanding Fees -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i> Outstanding Fees
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Fee Category</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outstandingFees as $fee)
                                <tr>
                                    <td>
                                        {{ $fee->student->user->name }}<br>
                                        <small class="text-muted">{{ $fee->student->admission_number }}</small>
                                     </span></td>
                                    <td>{{ $fee->student->class->name ?? 'N/A' }}</span></td>
                                    <td>{{ $fee->feeStructure->feeCategory->name ?? 'N/A' }}</span></td>
                                    <td>₹ {{ number_format($fee->total_amount, 2) }}</span></td>
                                    <td>₹ {{ number_format($fee->paid_amount, 2) }}</span></td>
                                    <td><span class="text-danger fw-bold">₹ {{ number_format($fee->due_amount, 2) }}</span></span></td>
                                    <td>{{ $fee->due_date->format('d-m-Y') }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $fee->status == 'overdue' ? 'danger' : 'warning' }}">
                                            {{ ucfirst($fee->status) }}
                                        </span>
                                     </span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $outstandingFees->appends(['outstanding_page' => $outstandingFees->currentPage()])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Payment Method Chart
    const methodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    const methodLabels = @json($summary['by_method']->pluck('payment_method')->map(function($method) {
        return ucfirst($method);
    }));
    const methodAmounts = @json($summary['by_method']->pluck('total'));
    
    new Chart(methodCtx, {
        type: 'doughnut',
        data: {
            labels: methodLabels,
            datasets: [{
                data: methodAmounts,
                backgroundColor: ['#4361ee', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    
    // Collection Trend Chart
    const trendCtx = document.getElementById('collectionTrendChart').getContext('2d');
    const trendDates = @json(collect($collectionTrend)->pluck('date')->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('d M');
    }));
    const trendAmounts = @json(collect($collectionTrend)->pluck('amount'));
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendDates,
            datasets: [{
                label: 'Amount Collected (₹)',
                data: trendAmounts,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Amount (₹)' }
                }
            }
        }
    });
    
    // Dynamic section loading
    $('#classFilter').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/admin/sections/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionFilter');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionFilter').empty().append('<option value="">All Sections</option>');
        }
    });
</script>
@endpush