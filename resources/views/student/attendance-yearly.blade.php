{{-- resources/views/student/attendance-yearly.blade.php --}}
@extends('layouts.student')

@section('title', 'Yearly Attendance Report')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <i class="fas fa-chart-line me-2"></i> 
                            Yearly Attendance Report - {{ $year }}
                        </div>
                        <div>
                            <form method="GET" action="{{ route('student.attendance.yearly') }}" class="d-inline">
                                <select name="year" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </form>
                            <a href="{{ route('student.attendance') }}" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Overall Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-gradient-primary text-white h-100" style="background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-alt fa-2x mb-2 opacity-75"></i>
                                    <h2 class="mb-0">{{ collect($yearlyData)->sum('total_days') }}</h2>
                                    <small>Total Working Days</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-gradient-success text-white h-100" style="background: linear-gradient(135deg, #06ffa5 0%, #00d4a0 100%);">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2 opacity-75"></i>
                                    <h2 class="mb-0">{{ collect($yearlyData)->sum('present') }}</h2>
                                    <small>Total Present Days</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-gradient-warning text-white h-100" style="background: linear-gradient(135deg, #ffd166 0%, #ffbe3c 100%);">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x mb-2 opacity-75"></i>
                                    <h2 class="mb-0">{{ $overallPercentage }}%</h2>
                                    <small>Overall Attendance</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-gradient-info text-white h-100" style="background: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);">
                                <div class="card-body text-center">
                                    <i class="fas fa-trophy fa-2x mb-2 opacity-75"></i>
                                    <h2 class="mb-0">
                                        @php
                                            $bestMonth = collect($yearlyData)->sortByDesc('percentage')->first();
                                        @endphp
                                        {{ $bestMonth ? $bestMonth['month'] : 'N/A' }}
                                    </h2>
                                    <small>Best Performing Month</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="fas fa-chart-line me-2 text-primary"></i> Monthly Attendance Trend</h6>
                                    <canvas id="yearlyAttendanceChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Data Table -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3"><i class="fas fa-table me-2 text-primary"></i> Monthly Breakdown</h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Month</th>
                                            <th>Total Days</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                            <th>Late</th>
                                            <th>Attendance %</th>
                                            <th>Status</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($yearlyData as $data)
                                            @php
                                                $attendanceClass = $data['percentage'] >= 75 ? 'success' : ($data['percentage'] >= 60 ? 'warning' : 'danger');
                                                $trendIcon = $data['percentage'] >= 75 ? 'arrow-up' : ($data['percentage'] >= 60 ? 'minus' : 'arrow-down');
                                                $trendColor = $data['percentage'] >= 75 ? 'success' : ($data['percentage'] >= 60 ? 'warning' : 'danger');
                                                $statusText = $data['percentage'] >= 75 ? 'Excellent' : ($data['percentage'] >= 60 ? 'Good' : 'Needs Improvement');
                                            @endphp
                                            <tr>
                                                <td class="fw-bold">{{ $data['month'] }}</td>
                                                <td>{{ $data['total_days'] }}</td>
                                                <td class="text-success fw-bold">{{ $data['present'] }}</td>
                                                <td class="text-danger">{{ $data['total_days'] - $data['present'] }}</td>
                                                <td>{{ $data['late'] ?? 0 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fw-bold text-{{ $attendanceClass }}">{{ $data['percentage'] }}%</span>
                                                        <div class="progress flex-grow-1" style="height: 6px;">
                                                            <div class="progress-bar bg-{{ $attendanceClass }}" style="width: {{ $data['percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $attendanceClass }}">{{ $statusText }}</span>
                                                </td>
                                                <td class="text-{{ $trendColor }}">
                                                    <i class="fas fa-{{ $trendIcon }} me-1"></i>
                                                    @if($data['percentage'] >= 75)
                                                        Excellent
                                                    @elseif($data['percentage'] >= 60)
                                                        Satisfactory
                                                    @else
                                                        Poor
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Insights & Recommendations -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="fas fa-chart-pie me-2 text-primary"></i> Attendance Insights</h6>
                                    <canvas id="attendanceDistributionChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i> Recommendations</h6>
                                    <div class="list-group list-group-flush bg-transparent">
                                        @if($overallPercentage >= 90)
                                            <div class="list-group-item bg-transparent border-0 px-0">
                                                <i class="fas fa-star text-success me-2"></i>
                                                Excellent attendance! Keep up the great work!
                                            </div>
                                        @elseif($overallPercentage >= 75)
                                            <div class="list-group-item bg-transparent border-0 px-0">
                                                <i class="fas fa-smile text-primary me-2"></i>
                                                Good attendance! Aim for 90%+ to be in the top tier.
                                            </div>
                                        @elseif($overallPercentage >= 60)
                                            <div class="list-group-item bg-transparent border-0 px-0">
                                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                Your attendance needs improvement. Regular attendance is crucial for academic success.
                                            </div>
                                        @else
                                            <div class="list-group-item bg-transparent border-0 px-0">
                                                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                                Low attendance detected. Please ensure regular attendance to avoid academic consequences.
                                            </div>
                                        @endif
                                        
                                        @php
                                            $worstMonth = collect($yearlyData)->sortBy('percentage')->first();
                                        @endphp
                                        @if($worstMonth && $worstMonth['percentage'] < 75)
                                            <div class="list-group-item bg-transparent border-0 px-0 mt-2">
                                                <i class="fas fa-calendar-times text-danger me-2"></i>
                                                Focus on improving attendance in {{ $worstMonth['month'] }} ({{ $worstMonth['percentage'] }}%).
                                            </div>
                                        @endif
                                        
                                        <div class="list-group-item bg-transparent border-0 px-0">
                                            <i class="fas fa-chart-line text-info me-2"></i>
                                            Your best attendance month: {{ $bestMonth ? $bestMonth['month'] . ' (' . $bestMonth['percentage'] . '%)' : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Report Button -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-download me-2"></i> Download Report (PDF)
                            </button>
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
    @media print {
        .sidebar, .topbar, .btn, .nav-tabs, .card-header .btn, .form-select, .alert {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        body {
            background: white !important;
        }
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #06ffa5 0%, #00d4a0 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffd166 0%, #ffbe3c 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
        cursor: pointer;
    }
    
    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Yearly Attendance Trend Chart
    var ctx1 = document.getElementById('yearlyAttendanceChart').getContext('2d');
    var yearlyChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($yearlyData)->pluck('month')) !!},
            datasets: [
                {
                    label: 'Attendance Percentage',
                    data: {!! json_encode(collect($yearlyData)->pluck('percentage')) !!},
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4361ee',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                },
                {
                    label: 'Target (75%)',
                    data: Array({!! collect($yearlyData)->count() !!}).fill(75),
                    borderColor: '#06ffa5',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw + '%';
                        }
                    }
                },
                legend: {
                    position: 'top',
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
    
    // Attendance Distribution Chart
    var ctx2 = document.getElementById('attendanceDistributionChart').getContext('2d');
    var distributionChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Excellent (75-100%)', 'Good (60-74%)', 'Needs Improvement (0-59%)'],
            datasets: [{
                data: [
                    {{ collect($yearlyData)->filter(function($item) { return $item['percentage'] >= 75; })->count() }},
                    {{ collect($yearlyData)->filter(function($item) { return $item['percentage'] >= 60 && $item['percentage'] < 75; })->count() }},
                    {{ collect($yearlyData)->filter(function($item) { return $item['percentage'] < 60; })->count() }}
                ],
                backgroundColor: ['#06ffa5', '#ffd166', '#ef476f'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' months';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
    
    // Animate progress bars on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressBars = entry.target.querySelectorAll('.progress-bar');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                });
                observer.unobserve(entry.target);
            }
        });
    });
    
    document.querySelectorAll('.table-responsive').forEach(el => {
        observer.observe(el);
    });
</script>
@endpush