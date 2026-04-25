{{-- resources/views/teacher/dashboard.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Dashboard')

@section('content')
<div class="animate-fadeInUp">
    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Classes</h6>
                            <h2 class="mb-0">{{ $quickStats['total_sections'] }}</h2>
                        </div>
                        <i class="fas fa-chalkboard-user fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Students</h6>
                            <h2 class="mb-0">{{ number_format($quickStats['total_students']) }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Pending Homework</h6>
                            <h2 class="mb-0">{{ $quickStats['pending_homework'] }}</h2>
                            <small>{{ $quickStats['submissions_to_grade'] }} to grade</small>
                        </div>
                        <i class="fas fa-book-open fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Today's Attendance</h6>
                            <h2 class="mb-0">{{ $quickStats['today_attendance_rate'] }}%</h2>
                        </div>
                        <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Schedule & Current Class -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i> Today's Schedule
                </div>
                <div class="card-body">
                    @if($todayTimetable->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr><th>Time</th><th>Class</th><th>Subject</th><th>Room</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($todayTimetable as $entry)
                                        <tr class="{{ $currentClass && $currentClass->id == $entry->id ? 'table-primary' : '' }}">
                                            <td>{{ \Carbon\Carbon::parse($entry->timeSlot->start_time)->format('h:i A') }} - 
                                                {{ \Carbon\Carbon::parse($entry->timeSlot->end_time)->format('h:i A') }}</td>
                                            <td>{{ $entry->class->name }} - Section {{ $entry->section->name }}</td>
                                            <td>{{ $entry->subject->name }} ({{ $entry->subject->code }})</td>
                                            <td>{{ $entry->room_number ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($currentClass)
                            <div class="alert alert-success mt-2">
                                <i class="fas fa-chalkboard-user me-2"></i> 
                                <strong>Current Class:</strong> {{ $currentClass->class->name }} - Section {{ $currentClass->section->name }} ({{ $currentClass->subject->name }})
                            </div>
                        @endif
                        @if($nextClass)
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-clock me-2"></i> 
                                <strong>Next Class:</strong> {{ $nextClass->class->name }} - Section {{ $nextClass->section->name }} ({{ $nextClass->subject->name }}) at 
                                {{ \Carbon\Carbon::parse($nextClass->timeSlot->start_time)->format('h:i A') }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No classes scheduled for today.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2"></i> Today's Attendance Summary
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h3 class="text-success">{{ $attendanceStats['present'] }}</h3>
                            <small>Present</small>
                        </div>
                        <div class="col-3">
                            <h3 class="text-danger">{{ $attendanceStats['absent'] }}</h3>
                            <small>Absent</small>
                        </div>
                        <div class="col-3">
                            <h3 class="text-warning">{{ $attendanceStats['late'] }}</h3>
                            <small>Late</small>
                        </div>
                        <div class="col-3">
                            <h3 class="text-info">{{ $attendanceStats['half_day'] }}</h3>
                            <small>Half Day</small>
                        </div>
                    </div>
                    <div class="progress mt-3">
                        <div class="progress-bar bg-success" style="width: {{ $attendanceStats['percentage'] }}%">
                            {{ $attendanceStats['percentage'] }}%
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-calendar-check me-1"></i> Mark Attendance
                        </a>
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
                    <i class="fas fa-chart-line me-2"></i> Monthly Attendance Trend
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i> Weekly Homework Activity
                </div>
                <div class="card-body">
                    <canvas id="homeworkChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Performance -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> Subject Performance (Average Marks)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Code</th>
                                    <th>Avg. Marks</th>
                                    <th>Avg. Percentage</th>
                                    <th>Exams Conducted</th>
                                    <th>Students</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectPerformance as $subject)
                                <tr>
                                    <td>{{ $subject['subject_name'] }}</td>
                                    <td>{{ $subject['subject_code'] }}</td>
                                    <td>{{ $subject['average_marks'] }}</td>
                                    <td>{{ $subject['average_percentage'] }}%</td>
                                    <td>{{ $subject['total_exams'] }}</td>
                                    <td>{{ $subject['total_students'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $subject['average_percentage'] >= 75 ? 'success' : ($subject['average_percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $subject['average_percentage'] }}%"></div>
                                        </div>
                                    </td>
                                </table>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Homework & Upcoming Exams -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-book-open me-2"></i> Pending Homework
                    @if($pendingHomework->count() > 0)
                        <span class="badge bg-danger float-end">{{ $pendingHomework->count() }}</span>
                    @endif
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($pendingHomework->count() > 0)
                        <div class="list-group">
                            @foreach($pendingHomework as $hw)
                                <a href="{{ route('teacher.homework.submissions', $hw) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $hw->title }}</strong>
                                        <span class="badge bg-info">{{ $hw->submissions_count }} submissions</span>
                                    </div>
                                    <div class="small">
                                        {{ $hw->class->name }} - Section {{ $hw->section->name }} | {{ $hw->subject->name }}
                                    </div>
                                    <div class="small text-muted">
                                        Due: {{ $hw->submission_date->format('d-m-Y') }}
                                        @if($hw->submission_date->isToday())
                                            <span class="badge bg-warning ms-2">Today</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No pending homework.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2"></i> Upcoming Exams
                    @if($upcomingExams->count() > 0)
                        <span class="badge bg-info float-end">{{ $upcomingExams->count() }}</span>
                    @endif
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($upcomingExams->count() > 0)
                        <div class="list-group">
                            @foreach($upcomingExams as $exam)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $exam->exam->name }}</strong>
                                        <span class="badge bg-warning">{{ \Carbon\Carbon::parse($exam->exam_date)->diffForHumans() }}</span>
                                    </div>
                                    <div class="small">
                                        {{ $exam->class->name }} - Section {{ $exam->section->name }} | {{ $exam->subject->name }}
                                    </div>
                                    <div class="small text-muted">
                                        Date: {{ $exam->exam_date->format('d-m-Y') }} | Time: {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}
                                    </div>
                                    <a href="{{ route('teacher.exams.students', $exam) }}" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-edit me-1"></i> Enter Marks
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No upcoming exams.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Results & Activities -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Recent Exam Results
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($recentResults->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Student</th><th>Exam</th><th>Subject</th><th>Marks</th><th>%</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($recentResults as $result)
                                    <tr>
                                        <td>{{ $result->student->user->name }}</td>
                                        <td>{{ $result->examSchedule->exam->name }}</td>
                                        <td>{{ $result->examSchedule->subject->name }}</td>
                                        <td>{{ $result->total_marks_obtained }}/{{ $result->examSchedule->total_marks }}</td>
                                        <td>{{ $result->percentage }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No exam results entered yet.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> Recent Activities
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($recentActivities->count() > 0)
                        <div class="timeline">
                            @foreach($recentActivities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-badge bg-{{ $activity['color'] }}">
                                        <i class="fas {{ $activity['icon'] }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ $activity['title'] }}</strong>
                                            <small class="text-muted">{{ $activity['time_ago'] }}</small>
                                        </div>
                                        <p class="mb-0 small">{{ $activity['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No recent activities.</p>
                    @endif
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
        padding-left: 40px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-badge {
        position: absolute;
        left: -40px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    .timeline-badge.bg-primary { background: #4361ee; }
    .timeline-badge.bg-success { background: #28a745; }
    .timeline-badge.bg-info { background: #17a2b8; }
    .timeline-badge.bg-warning { background: #ffc107; color: #333; }
    .timeline-content {
        border-left: 2px solid #e2e8f0;
        padding-left: 15px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyAttendance['labels']),
            datasets: [
                {
                    label: 'Present',
                    data: @json($monthlyAttendance['present']),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Absent',
                    data: @json($monthlyAttendance['absent']),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,0.1)',
                    fill: true,
                    tension: 0.4
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
    
    // Weekly Homework Chart
    const homeworkCtx = document.getElementById('homeworkChart').getContext('2d');
    new Chart(homeworkCtx, {
        type: 'bar',
        data: {
            labels: @json($weeklyHomework['labels']),
            datasets: [
                {
                    label: 'Homework Assigned',
                    data: @json($weeklyHomework['assigned']),
                    backgroundColor: '#4361ee',
                    borderRadius: 5
                },
                {
                    label: 'Submissions Received',
                    data: @json($weeklyHomework['submitted']),
                    backgroundColor: '#28a745',
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
</script>
@endpush