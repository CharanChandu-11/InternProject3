{{-- resources/views/student/results/summary.blade.php --}}
@extends('layouts.student')

@section('title', 'Performance Summary')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-pie me-2"></i> Performance Summary
            <div class="float-end">
                <a href="{{ route('student.results') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-list me-1"></i> View Results
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Year Filter -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="year" class="form-select">
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('student.results.summary') }}" class="btn btn-secondary">Current Year</a>
                    </div>
                </div>
            </form>
            
            <!-- Overall Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Exams</h6>
                            <h2>{{ $stats['total_exams'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Overall Average</h6>
                            <h2>{{ $stats['overall_percentage'] }}%</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Pass Percentage</h6>
                            <h2>{{ $stats['pass_percentage'] }}%</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Total Subjects</h6>
                            <h2>{{ $stats['total_subjects'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Best/Worst Subjects -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-trophy me-2"></i> Best Subject
                        </div>
                        <div class="card-body text-center">
                            @if($stats['best_subject'])
                                <h3>{{ $stats['best_subject']['subject'] }}</h3>
                                <div class="display-4 text-success">{{ $stats['best_subject']['average'] }}%</div>
                                <p class="text-muted">Average across all exams</p>
                            @else
                                <p>No data available</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-chart-line me-2"></i> Needs Improvement
                        </div>
                        <div class="card-body text-center">
                            @if($stats['worst_subject'])
                                <h3>{{ $stats['worst_subject']['subject'] }}</h3>
                                <div class="display-4 text-danger">{{ $stats['worst_subject']['average'] }}%</div>
                                <p class="text-muted">Focus area for improvement</p>
                            @else
                                <p>No data available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Exam Trend Chart -->
            <div class="mb-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-chart-line me-2 text-primary"></i> Performance Trend</h6>
                        <canvas id="examTrendChart" class="wh-100"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Performance Chart -->
            <div class="mb-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i> Monthly Performance</h6>
                        <canvas id="monthlyChart" class="wh-100"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Subject Performance Table -->
            <h5 class="mb-3">Subject-wise Performance</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Code</th>
                            <th class="text-center">Average</th>
                            <th class="text-center">Best</th>
                            <th class="text-center">Worst</th>
                            <th class="text-center">Exams Taken</th>
                            <th class="text-center">Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjectPerformance as $subject)
                        <tr>
                            <td><strong>{{ $subject['subject'] }}</strong></td>
                            <td>{{ $subject['code'] }}</span></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="me-2">{{ $subject['average'] }}%</span>
                                    <div class="progress" style="width: 80px; height: 6px;">
                                        <div class="progress-bar bg-{{ $subject['average'] >= 75 ? 'success' : ($subject['average'] >= 60 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $subject['average'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center text-success">{{ $subject['best'] }}%</td>
                            <td class="text-center text-danger">{{ $subject['worst'] }}%</td>
                            <td class="text-center">{{ $subject['exams_count'] }}</td>
                            <td class="text-center">
                                @if($subject['average'] >= 75)
                                    <span class="badge bg-success">Good</span>
                                @elseif($subject['average'] >= 60)
                                    <span class="badge bg-warning">Average</span>
                                @else
                                    <span class="badge bg-danger">Needs Improvement</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            
            <!-- Grade Distribution -->
            <h5 class="mb-3">Grade Distribution</h5>
            <div class="row">
                <div class="col-md-6">
                    <canvas id="gradeChart" class="wh-100"></canvas>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th>Grade</th><th>Count</th><th>Percentage</th></tr>
                            </thead>
                            <tbody>
                                @foreach($stats['grade_distribution'] as $grade => $count)
                                    @if($count > 0)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $grade == 'F' ? 'danger' : ($grade == 'A+' ? 'success' : 'info') }}">
                                                    {{ $grade }}
                                                </span>
                                            </td>
                                            <td>{{ $count }}</td>
                                            <td>{{ round(($count / $stats['total_subjects']) * 100, 2) }}%</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
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
    // Exam Trend Chart
    const examTrendCtx = document.getElementById('examTrendChart').getContext('2d');
    const examLabels = @json(collect($examTrend)->pluck('exam_name')->toArray());
    const examData = @json(collect($examTrend)->pluck('average')->toArray());
    
    new Chart(examTrendCtx, {
        type: 'line',
        data: {
            labels: examLabels,
            datasets: [{
                label: 'Percentage (%)',
                data: examData,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4361ee',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentage (%)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Monthly Performance Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyLabels = @json(collect($monthlyPerformance)->pluck('month')->toArray());
    const monthlyData = @json(collect($monthlyPerformance)->pluck('average')->toArray());
    
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Average Percentage (%)',
                data: monthlyData,
                backgroundColor: monthlyData.map(p => p >= 75 ? 'rgba(40, 167, 69, 0.7)' : (p >= 60 ? 'rgba(255, 193, 7, 0.7)' : 'rgba(220, 53, 69, 0.7)')),
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentage (%)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Grade Distribution Chart
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    const gradeLabels = @json(array_keys($stats['grade_distribution']));
    const gradeCounts = @json(array_values($stats['grade_distribution']));
    
    new Chart(gradeCtx, {
        type: 'doughnut',
        data: {
            labels: gradeLabels,
            datasets: [{
                data: gradeCounts,
                backgroundColor: ['#28a745', '#20c997', '#ffc107', '#17a2b8', '#6c757d', '#fd7e14', '#dc3545'],
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