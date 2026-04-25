{{-- resources/views/student/attendance/yearly.blade.php --}}
@extends('layouts.student')

@section('title', 'Yearly Attendance - Academic Year ' . $academicYear)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line me-2"></i> Yearly Attendance Summary
            <div class="float-end">
                <a href="{{ route('student.attendance') }}" class="btn btn-sm btn-secondary me-1">
                    <i class="fas fa-list me-1"></i> List View
                </a>
                <a href="{{ route('student.attendance.monthly') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-calendar-week me-1"></i> Calendar View
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Academic Year Selector -->
            <form method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Academic Year</label>
                        <select name="academic_year" class="form-select">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay }}" {{ $academicYear == $ay ? 'selected' : '' }}>
                                    Academic Year {{ $ay }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> View
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('student.attendance.yearly') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-refresh me-1"></i> Current Year
                        </a>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="info-box p-2 bg-light rounded">
                            <i class="fas fa-info-circle text-primary me-1"></i>
                            <small>Academic year runs from April to March</small>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Academic Year Info Banner -->
            <div class="alert alert-primary mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <i class="fas fa-calendar-alt me-2"></i>
                        <strong>Academic Year:</strong> {{ $academicYear }}
                        <span class="mx-2">|</span>
                        <i class="fas fa-calendar-day me-1"></i>
                        {{ $yearlyStats['start_date'] }} to {{ $yearlyStats['end_date'] }}
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-chart-line me-1"></i>
                            {{ $yearlyStats['total_days'] }} days recorded
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Yearly Stats Cards -->
            <div class="row mb-4 g-3">
                <div class="col-md-3">
                    <div class="card bg-gradient-primary text-white border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title opacity-75">Total Days</h6>
                                    <h2 class="mb-0 fw-bold">{{ $yearlyStats['total_days'] }}</h2>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-success text-white border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title opacity-75">Present</h6>
                                    <h2 class="mb-0 fw-bold">{{ $yearlyStats['total_present'] }}</h2>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-danger text-white border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title opacity-75">Absent</h6>
                                    <h2 class="mb-0 fw-bold">{{ $yearlyStats['total_absent'] }}</h2>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-info text-white border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title opacity-75">Overall Percentage</h6>
                                    <h2 class="mb-0 fw-bold">{{ $yearlyStats['overall_percentage'] }}%</h2>
                                </div>
                                <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                    <i class="fas fa-chart-pie fa-2x"></i>
                                </div>
                            </div>
                            <div class="progress mt-2 bg-light bg-opacity-25" style="height: 5px;">
                                <div class="progress-bar bg-white" style="width: {{ $yearlyStats['overall_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Best/Worst Months Cards -->
            <div class="row mb-4 g-3">
                <div class="col-md-6">
                    <div class="card border-success border-2 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-success p-2 me-3">
                                    <i class="fas fa-trophy text-white"></i>
                                </div>
                                <h5 class="mb-0 text-success">Best Month</h5>
                            </div>
                            @if($yearlyStats['best_month'])
                                <div class="text-center py-3">
                                    <h3 class="mb-2">{{ $yearlyStats['best_month']['month'] }} {{ $yearlyStats['best_month']['year'] }}</h3>
                                    <div class="display-4 fw-bold text-success mb-2">{{ $yearlyStats['best_month']['percentage'] }}%</div>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        {{ $yearlyStats['best_month']['present'] }}/{{ $yearlyStats['best_month']['total_days'] }} days present
                                    </p>
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No data available</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-warning border-2 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-warning p-2 me-3">
                                    <i class="fas fa-chart-line text-dark"></i>
                                </div>
                                <h5 class="mb-0 text-warning">Needs Improvement</h5>
                            </div>
                            @if($yearlyStats['worst_month'])
                                <div class="text-center py-3">
                                    <h3 class="mb-2">{{ $yearlyStats['worst_month']['month'] }} {{ $yearlyStats['worst_month']['year'] }}</h3>
                                    <div class="display-4 fw-bold text-warning mb-2">{{ $yearlyStats['worst_month']['percentage'] }}%</div>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        {{ $yearlyStats['worst_month']['present'] }}/{{ $yearlyStats['worst_month']['total_days'] }} days present
                                    </p>
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No data available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart -->
            <div class="mb-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-chart-line me-2 text-primary"></i> Monthly Attendance Trend</h6>
                        <canvas id="attendanceChart" class="mh-100"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Data Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-table me-2 text-primary"></i> Monthly Breakdown</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th class="text-center">Total Days</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center">Late</th>
                                    <th class="text-center">Half Day</th>
                                    <th class="text-center">Percentage</th>
                                    <th class="text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyData as $index => $data)
                                    <tr>
                                        <td class="fw-bold">{{ $data['month'] }}</td>
                                        <td>{{ $data['year'] }}</td>
                                        <td class="text-center">{{ $data['total_days'] }}</td>
                                        <td class="text-center text-success fw-bold">{{ $data['present'] }}</td>
                                        <td class="text-center text-danger">{{ $data['absent'] }}</td>
                                        <td class="text-center text-warning">{{ $data['late'] }}</td>
                                        <td class="text-center text-info">{{ $data['half_day'] }}</td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <span class="me-2 fw-bold">{{ $data['percentage'] }}%</span>
                                                <div class="progress" style="width: 80px; height: 6px;">
                                                    <div class="progress-bar bg-{{ $data['percentage'] >= 75 ? 'success' : ($data['percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                                         style="width: {{ $data['percentage'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($index > 0)
                                                @php
                                                    $prevPercentage = $monthlyData[$index - 1]['percentage'];
                                                    $change = round($data['percentage'] - $prevPercentage, 2);
                                                @endphp
                                                @if($change > 0)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-arrow-up me-1"></i> +{{ $change }}%
                                                    </span>
                                                @elseif($change < 0)
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-arrow-down me-1"></i> {{ $change }}%
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-minus me-1"></i> 0%
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Start</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Summary Notes -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-secondary">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-simple me-2"></i>
                                <strong>Summary:</strong>
                                You have maintained 
                                <span class="fw-bold text-{{ $yearlyStats['overall_percentage'] >= 75 ? 'success' : ($yearlyStats['overall_percentage'] >= 60 ? 'warning' : 'danger') }}">
                                    {{ $yearlyStats['overall_percentage'] }}%
                                </span> 
                                attendance in Academic Year {{ $academicYear }}.
                            </div>
                            @if($yearlyStats['overall_percentage'] < 75)
                                <div class="mt-2 mt-sm-0">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Aim for 75%+ attendance for better academic performance.
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .table tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
    }
    .info-box {
        font-size: 13px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Prepare data in JavaScript (PHP data passed directly)
    const monthlyLabels = @json(collect($monthlyData)->pluck('month')->toArray());
    const monthlyPercentages = @json(collect($monthlyData)->pluck('percentage')->toArray());
    const monthlyDetails = @json($monthlyData);
    
    // Define colors based on percentage values
    const backgroundColors = monthlyPercentages.map(function(percentage) {
        if (percentage >= 75) return 'rgba(40, 167, 69, 0.2)';
        if (percentage >= 60) return 'rgba(255, 193, 7, 0.2)';
        return 'rgba(220, 53, 69, 0.2)';
    });
    
    const borderColors = monthlyPercentages.map(function(percentage) {
        if (percentage >= 75) return '#28a745';
        if (percentage >= 60) return '#ffc107';
        return '#dc3545';
    });
    
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Attendance Percentage (%)',
                data: monthlyPercentages,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 2,
                borderRadius: 8,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Attendance: ${context.raw}%`;
                        },
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            if (monthlyDetails[index]) {
                                return [
                                    `Present: ${monthlyDetails[index].present} days`,
                                    `Total Days: ${monthlyDetails[index].total_days} days`
                                ];
                            }
                            return [];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Attendance Percentage (%)',
                        font: {
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        stepSize: 10
                    },
                    grid: {
                        color: '#e9ecef'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Months',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endpush