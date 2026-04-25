{{-- resources/views/student/dashboard.blade.php --}}
@extends('layouts.student')

@section('title', 'Dashboard')

@section('content')
<div class="animate-fadeInUp">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">Welcome back, {{ $student->user->name }}!</h2>
                            <p class="mb-0 mt-2 opacity-75">
                                <i class="fas fa-graduation-cap me-2"></i>
                                {{ $student->class_name }} - Section {{ $student->section_name }} | 
                                Roll No: {{ $student->roll_number ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <img src="{{ $student->profile_photo_url }}" alt="Profile" 
                                 style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid white;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Attendance</h6>
                            <h3 class="mb-0">{{ $attendanceStats['overall_percentage'] }}%</h3>
                        </div>
                        <div class="rounded-circle p-3 bg-success-light">
                            <i class="fas fa-calendar-check fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: {{ $attendanceStats['overall_percentage'] }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        {{ $attendanceStats['monthly']['present'] }} / {{ $attendanceStats['monthly']['total'] }} days present this month
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Average Marks</h6>
                            <h3 class="mb-0">{{ $performanceStats['average_percentage'] }}%</h3>
                        </div>
                        <div class="rounded-circle p-3 bg-primary-light">
                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Based on {{ $performanceStats['total_exams'] }} exams
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Fees</h6>
                            <h3 class="mb-0 text-warning">{{ $quickStats['pending_fees_formatted'] }}</h3>
                        </div>
                        <div class="rounded-circle p-3 bg-warning-light">
                            <i class="fas fa-rupee-sign fa-2x text-warning"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        {{ $feeStatus->count() }} pending installments
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Homework</h6>
                            <h3 class="mb-0">{{ $quickStats['pending_homework'] }}</h3>
                        </div>
                        <div class="rounded-circle p-3 bg-info-light">
                            <i class="fas fa-book-open fa-2x text-info"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Submit before due date
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Schedule -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i> Today's Schedule
                </div>
                <div class="card-body">
                    @if($todayTimetable->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Time</th><th>Subject</th><th>Teacher</th><th>Room</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($todayTimetable as $class)
                                    <tr class="{{ $currentClass && $currentClass->id == $class->id ? 'table-primary' : '' }}">
                                        <td>{{ $class->timeSlot->time_range }}</td>
                                        <td><strong>{{ $class->subject->name }}</strong></td>
                                        <td>{{ $class->teacher->name }}</td>
                                        <td>{{ $class->room_number ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($currentClass)
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="fas fa-chalkboard-user me-2"></i>
                                <strong>Current Class:</strong> {{ $currentClass->subject->name }} with {{ $currentClass->teacher->name }}
                            </div>
                        @endif
                        @if($nextClass)
                            <div class="alert alert-info mt-2 mb-0">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Next Class:</strong> {{ $nextClass->subject->name }} at {{ $nextClass->timeSlot->start_time->format('h:i A') }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No classes scheduled for today.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Today's Attendance -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2"></i> Today's Attendance
                </div>
                <div class="card-body text-center">
                    @if($todayAttendance)
                        <div class="mb-3">
                            @if($todayAttendance->status == 'present')
                                <i class="fas fa-check-circle text-success" style="font-size: 60px;"></i>
                                <h3 class="text-success mt-2">Present</h3>
                            @elseif($todayAttendance->status == 'absent')
                                <i class="fas fa-times-circle text-danger" style="font-size: 60px;"></i>
                                <h3 class="text-danger mt-2">Absent</h3>
                            @elseif($todayAttendance->status == 'late')
                                <i class="fas fa-clock text-warning" style="font-size: 60px;"></i>
                                <h3 class="text-warning mt-2">Late</h3>
                            @else
                                <i class="fas fa-sun text-info" style="font-size: 60px;"></i>
                                <h3 class="text-info mt-2">Half Day</h3>
                            @endif
                        </div>
                        @if($todayAttendance->check_in_time)
                            <p class="mb-1"><strong>Check In:</strong> {{ $todayAttendance->check_in_time->format('h:i A') }}</p>
                        @endif
                        @if($todayAttendance->check_out_time)
                            <p class="mb-0"><strong>Check Out:</strong> {{ $todayAttendance->check_out_time->format('h:i A') }}</p>
                        @endif
                    @else
                        <i class="fas fa-question-circle text-secondary" style="font-size: 60px;"></i>
                        <h3 class="text-secondary mt-2">Not Marked Yet</h3>
                        <p class="text-muted">Attendance will be updated by your teacher.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Homework -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-book-open me-2"></i> Pending Homework
                    <a href="{{ route('student.homework') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body">
                    @if(count($pendingHomeworkData) > 0)
                        @foreach($pendingHomeworkData as $item)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $item['homework']->title }}</h6>
                                        <p class="text-muted small mb-1">
                                            <i class="fas fa-book me-1"></i> {{ $item['homework']->subject->name }}
                                        </p>
                                        <p class="text-muted small">
                                            <i class="fas fa-calendar-alt me-1"></i> 
                                            Due: {{ $item['homework']->submission_date->format('d M, Y') }}
                                            @if($item['homework']->submission_date->isToday())
                                                <span class="badge bg-warning ms-2">Today</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if($item['submitted'])
                                        <span class="badge bg-success">Submitted</span>
                                    @else
                                        <a href="{{ route('student.homework.show', $item['homework']) }}" class="btn btn-sm btn-primary">
                                            Submit
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No pending homework. Great job!</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Upcoming Exams -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2"></i> Upcoming Exams
                    <a href="{{ route('student.exams') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body">
                    @if($upcomingExams->count() > 0)
                        @foreach($upcomingExams as $exam)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $exam->subject->name }}</h6>
                                        <p class="text-muted small mb-1">{{ $exam->exam->name }}</p>
                                        <p class="text-muted small">
                                            <i class="fas fa-calendar-alt me-1"></i> 
                                            {{ $exam->exam_date->format('d M, Y') }}
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">
                                            {{ now()->diffInDays($exam->exam_date, false) }} days left
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No upcoming exams. Enjoy your break!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Results -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Recent Results
                    <a href="{{ route('student.results') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body">
                    @if($recentResults->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Exam</th><th>Subject</th><th>Marks</th><th>Grade</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($recentResults as $result)
                                    <tr>
                                        <td>{{ $result->examSchedule->exam->name }}</td>
                                        <td>{{ $result->examSchedule->subject->name }}</td>
                                        <td>{{ $result->total_marks_obtained }}/{{ $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0) }}</td>
                                        <td><span class="badge bg-{{ $result->grade == 'F' ? 'danger' : 'success' }}">{{ $result->grade }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No results published yet.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Fee Status -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-rupee-sign me-2"></i> Fee Status
                    <a href="{{ route('student.fees') }}" class="float-end text-decoration-none">View Details</a>
                </div>
                <div class="card-body">
                    @if($feeStatus->count() > 0)
                        <div class="list-group">
                            @foreach($feeStatus as $fee)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $fee->feeStructure->feeCategory->name }}</strong>
                                            <br>
                                            <small class="text-muted">Due Date: {{ $fee->due_date->format('d M, Y') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-danger">₹ {{ number_format($fee->due_amount, 2) }}</span>
                                            <br>
                                            <small class="text-muted">Due</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-danger" style="width: {{ ($fee->paid_amount / $fee->total_amount) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 text-center">
                            <strong>Total Due: ₹ {{ number_format($totalDue, 2) }}</strong>
                        </div>
                    @else
                        <p class="text-muted text-center">No pending fees. All payments up to date!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Announcements Section -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bullhorn me-2"></i> Announcements
                    <a href="{{ route('website.news') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    @if($announcements->count() > 0)
                        @foreach($announcements as $announcement)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <div class="bg-primary text-white rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-bullhorn"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1">{{ $announcement->title }}</h6>
                                            <small class="text-muted">{{ $announcement->publish_date->diffForHumans() }}</small>
                                        </div>
                                        <p class="text-muted small mb-1">{{ Str::limit(strip_tags($announcement->content), 100) }}</p>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-primary" data-bs-toggle="modal" 
                                                data-bs-target="#announcementModal{{ $announcement->id }}">
                                            Read More
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Announcement Modal -->
                            <div class="modal fade" id="announcementModal{{ $announcement->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $announcement->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    Published on: {{ $announcement->publish_date->format('F j, Y') }}
                                                    @if($announcement->expiry_date)
                                                        | Expires: {{ $announcement->expiry_date->format('F j, Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                            <div class="mt-3">
                                                {!! nl2br(e($announcement->content)) !!}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No announcements available.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Library Books -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-book me-2"></i> Library Books
                    <a href="{{ route('student.library.books') }}" class="float-end text-decoration-none">Browse</a>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    @if($libraryBooks->count() > 0)
                        @foreach($libraryBooks as $book)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $book->book->title }}</strong>
                                        <br>
                                        <small class="text-muted">Author: {{ $book->book->author }}</small>
                                        <br>
                                        <small class="text-muted">Issued: {{ $book->issue_date->format('d M, Y') }}</small>
                                    </div>
                                    <div>
                                        @if($book->due_date->isPast())
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-info">{{ now()->diffInDays($book->due_date, false) }} days left</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No books issued currently.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Transport Details -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bus me-2"></i> Transport Details
                </div>
                <div class="card-body">
                    @if($transport)
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="fas fa-route me-2"></i> Route:</strong></p>
                                <p>{{ $transport->route->route_name }} ({{ $transport->route->route_number }})</p>
                                
                                <p class="mb-2"><strong><i class="fas fa-map-marker-alt me-2"></i> Stop:</strong></p>
                                <p>{{ $transport->stop->stop_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="fas fa-clock me-2"></i> Pickup Time:</strong></p>
                                <p>{{ \Carbon\Carbon::parse($transport->stop->pickup_time)->format('h:i A') }}</p>
                                
                                <p class="mb-2"><strong><i class="fas fa-clock me-2"></i> Drop Time:</strong></p>
                                <p>{{ \Carbon\Carbon::parse($transport->stop->drop_time)->format('h:i A') }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center">No transport allocated.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Hostel Details -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-hotel me-2"></i> Hostel Details
                </div>
                <div class="card-body">
                    @if($hostel)
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="fas fa-building me-2"></i> Hostel:</strong></p>
                                <p>{{ $hostel->room->hostel->name }}</p>
                                
                                <p class="mb-2"><strong><i class="fas fa-door-open me-2"></i> Room No:</strong></p>
                                <p>{{ $hostel->room->room_number }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="fas fa-bed me-2"></i> Room Type:</strong></p>
                                <p>{{ ucfirst($hostel->room->room_type) }}</p>
                                
                                <p class="mb-2"><strong><i class="fas fa-rupee-sign me-2"></i> Fee/Month:</strong></p>
                                <p>₹ {{ number_format($hostel->room->fee_per_month, 2) }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center">No hostel allocated.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Events -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Upcoming Events
                    <a href="{{ route('website.events') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body">
                    @if($upcomingEvents->count() > 0)
                        @foreach($upcomingEvents as $event)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $event->title }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i> {{ $event->start_date->format('d M, Y') }}
                                            <span class="mx-1">|</span>
                                            <i class="fas fa-map-marker-alt me-1"></i> {{ $event->venue }}
                                        </small>
                                    </div>
                                    <span class="badge bg-info">{{ now()->diffInDays($event->start_date, false) }} days left</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No upcoming events.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recent Notifications -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bell me-2"></i> Recent Notifications
                    <a href="{{ route('notifications.index') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($recentNotifications->count() > 0)
                        @foreach($recentNotifications as $notification)
                            <div class="border-bottom pb-2 mb-2 {{ !$notification->is_read ? 'bg-light p-2 rounded' : '' }}">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <i class="fas fa-bell text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{{ $notification->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $notification->message }}</small>
                                        <br>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No notifications.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1);
    }
    .bg-primary-light {
        background-color: rgba(67, 97, 238, 0.1);
    }
    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
    }
    .bg-info-light {
        background-color: rgba(23, 162, 184, 0.1);
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .list-group-item {
        transition: background-color 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush
