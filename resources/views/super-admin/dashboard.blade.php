{{-- resources/views/super-admin/dashboard.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Dashboard')

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Students</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_students']) }}</h2>
                        </div>
                        <i class="fas fa-graduation-cap fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Teachers</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_teachers']) }}</h2>
                        </div>
                        <i class="fas fa-chalkboard-user fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Employees</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_employees']) }}</h2>
                        </div>
                        <i class="fas fa-user-tie fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Parents</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_parents']) }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 2 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Monthly Revenue</h6>
                            <h3 class="mb-0 text-success">₹ {{ number_format($financial['monthly_revenue'], 2) }}</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-3x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Yearly Revenue</h6>
                            <h3 class="mb-0 text-primary">₹ {{ number_format($financial['yearly_revenue'], 2) }}</h3>
                        </div>
                        <i class="fas fa-chart-line fa-3x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Pending Fees</h6>
                            <h3 class="mb-0 text-danger">₹ {{ number_format($financial['pending_fees'], 2) }}</h3>
                        </div>
                        <i class="fas fa-clock fa-3x text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Total Books</h6>
                            <h3 class="mb-0 text-info">{{ number_format($stats['total_books']) }}</h3>
                            <small>{{ $stats['books_issued'] }} issued</small>
                        </div>
                        <i class="fas fa-book-open fa-3x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Attendance Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2"></i> Today's Attendance
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-success">{{ number_format($todayAttendance['present']) }}</h3>
                            <p>Present</p>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: {{ $todayAttendance['present_percent'] }}%"></div>
                            </div>
                            <small>{{ $todayAttendance['present_percent'] }}%</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger">{{ number_format($todayAttendance['absent']) }}</h3>
                            <p>Absent</p>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: {{ $todayAttendance['absent_percent'] }}%"></div>
                            </div>
                            <small>{{ $todayAttendance['absent_percent'] }}%</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ number_format($todayAttendance['late']) }}</h3>
                            <p>Late</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info">{{ number_format($todayAttendance['total']) }}</h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Monthly Revenue Trend
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Monthly Attendance Trend
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Enrollment by Class -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Student Enrollment by Class
                </div>
                <div class="card-body">
                    <canvas id="enrollmentChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Payments Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> Recent Activities
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="timeline">
                        @foreach($recentActivities as $activity)
                            <div class="timeline-item">
                                <div class="timeline-badge">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $activity['user'] }}</strong>
                                        <small class="text-muted">{{ $activity['time_ago'] }}</small>
                                    </div>
                                    <p class="mb-0">{{ $activity['description'] }}</p>
                                    <small class="text-muted">{{ ucfirst($activity['action']) }} in {{ $activity['module'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-credit-card me-2"></i> Recent Payments
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Receipt No</th><th>Student</th><th>Amount</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment['payment_number'] }}</td>
                                    <td>{{ $payment['student'] }}<br><small>{{ $payment['admission_number'] }}</small></td>
                                    <td><span class="fw-bold text-success">{{ $payment['amount_formatted'] }}</span></td>
                                    <td>{{ $payment['payment_date'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Events and Recent Students Row -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Upcoming Events
                </div>
                <div class="card-body">
                    @foreach($upcomingEvents as $event)
                        <div class="event-item mb-2 pb-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $event['title'] }}</strong>
                                <span class="badge bg-info">{{ $event['days_left'] }} days left</span>
                            </div>
                            <div>{{ $event['date'] }} | {{ $event['venue'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus me-2"></i> Recent Students
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Name</th><th>Admission No</th><th>Class</th><th>Admission Date</th></tr>
                            </thead>
                            <tbody>
                                @foreach($recentStudents as $student)
                                <tr>
                                    <td>{{ $student['name'] }}</td>
                                    <td>{{ $student['admission_number'] }}</td>
                                    <td>{{ $student['class'] }}</td>
                                    <td>{{ $student['admission_date'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Card -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-server me-2"></i> System Health
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fas fa-database fa-2x text-primary"></i>
                                <h6>Database Size</h6>
                                <p>{{ $systemHealth['database_size'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fas fa-hdd fa-2x text-info"></i>
                                <h6>Storage Usage</h6>
                                <p>{{ $systemHealth['storage_usage']['used'] }} / {{ $systemHealth['storage_usage']['total'] }}</p>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: {{ $systemHealth['storage_usage']['percent'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fas fa-code-branch fa-2x text-success"></i>
                                <h6>Laravel Version</h6>
                                <p>{{ $systemHealth['laravel_version'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fab fa-php fa-2x text-warning"></i>
                                <h6>PHP Version</h6>
                                <p>{{ $systemHealth['php_version'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-{{ $systemHealth['backup_exists'] ? 'success' : 'warning' }}">
                                <i class="fas {{ $systemHealth['backup_exists'] ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-2"></i>
                                Backup Status: {{ $systemHealth['backup_exists'] ? 'Available' : 'Not Available' }}
                                @if($systemHealth['last_backup'])
                                    <small class="ms-2">Last Backup: {{ $systemHealth['last_backup'] }}</small>
                                @endif
                            </div>
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
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-badge {
        position: absolute;
        left: -30px;
        top: 0;
        width: 20px;
        height: 20px;
        color: #4361ee;
    }
    .timeline-content {
        padding-left: 15px;
        border-left: 2px solid #e2e8f0;
    }
    .event-item:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyLabels),
            datasets: [{
                label: 'Revenue (₹)',
                data: @json($monthlyRevenue),
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67,97,238,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
    
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: @json($attendanceLabels),
            datasets: [
                {
                    label: 'Present',
                    data: @json(array_column($monthlyAttendance, 'present')),
                    backgroundColor: '#28a745',
                    borderRadius: 5
                },
                {
                    label: 'Absent',
                    data: @json(array_column($monthlyAttendance, 'absent')),
                    backgroundColor: '#dc3545',
                    borderRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
    
    // Enrollment Chart
    const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(enrollmentCtx, {
        type: 'bar',
        data: {
            labels: @json($classLabels),
            datasets: [{
                label: 'Number of Students',
                data: @json($classEnrollment),
                backgroundColor: '#17a2b8',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
</script>
@endpush