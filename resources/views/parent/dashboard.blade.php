{{-- resources/views/parent/dashboard.blade.php --}}
@extends('layouts.parent')

@section('title', 'Dashboard')

@section('content')
<div class="animate-fadeInUp">
    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">My Children</h6>
                            <h2 class="mb-0">{{ $quickStats['total_children'] }}</h2>
                        </div>
                        <i class="fas fa-child fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Present Today</h6>
                            <h2 class="mb-0">{{ $quickStats['present_today'] }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Absent Today</h6>
                            <h2 class="mb-0">{{ $quickStats['absent_today'] }}</h2>
                        </div>
                        <i class="fas fa-times-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Due Fees</h6>
                            <h2 class="mb-0">{{ $quickStats['total_due_fees_formatted'] }}</h2>
                        </div>
                        <i class="fas fa-rupee-sign fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Children Cards -->
    <div class="row">
        @foreach($childrenData as $child)
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-user-graduate me-2"></i> {{ $child['student']->user->name }}
                    <span class="float-end badge bg-primary">{{ $child['student']->admission_number }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Class:</strong> {{ $child['student']->class_name }}</p>
                            <p><strong>Section:</strong> {{ $child['student']->section_name }}</p>
                            <p><strong>Roll No:</strong> {{ $child['student']->roll_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Attendance:</strong> 
                                <span class="badge bg-{{ $child['attendance_percentage'] >= 75 ? 'success' : ($child['attendance_percentage'] >= 60 ? 'warning' : 'danger') }}">
                                    {{ $child['attendance_percentage'] }}%
                                </span>
                            </p>
                            <p><strong>Today:</strong> 
                                <span class="badge bg-{{ $child['today_attendance']?->status == 'present' ? 'success' : 'danger' }}">
                                    {{ ucfirst($child['today_attendance']?->status ?? 'Not Marked') }}
                                </span>
                            </p>
                            <p><strong>Pending Fees:</strong> 
                                <span class="text-danger">{{ $child['pending_fees_formatted'] }}</span>
                            </p>
                        </div>
                    </div>
                    
                    @if($child['latest_result'])
                        <div class="alert alert-info mt-2">
                            <strong>Latest Result:</strong> {{ $child['latest_result']->examSchedule->subject->name }} - 
                            {{ $child['latest_result']->percentage }}% ({{ $child['latest_result']->grade }})
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        <div class="btn-group w-100">
                            <a href="{{ route('parent.children.attendance', $child['student']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-calendar-check"></i> Attendance
                            </a>
                            <a href="{{ route('parent.children.results', $child['student']) }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-chart-line"></i> Results
                            </a>
                            <a href="{{ route('parent.children.fees', $child['student']) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-rupee-sign"></i> Fees
                            </a>
                            <a href="{{ route('parent.children.homework', $child['student']) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-book-open"></i> Homework
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        <!-- Upcoming Events -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Upcoming Events
                </div>
                <div class="card-body">
                    @if($upcomingEvents->count() > 0)
                        @foreach($upcomingEvents as $event)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $event->title }}</strong>
                                    <span class="badge bg-info">{{ now()->diffInDays($event->start_date, false) }} days left</span>
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> {{ $event->start_date->format('d M, Y') }}
                                    @if($event->venue)
                                        | <i class="fas fa-map-marker-alt me-1"></i> {{ $event->venue }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No upcoming events.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recent Messages -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-envelope me-2"></i> Recent Messages
                    <a href="{{ route('parent.messages') }}" class="float-end text-decoration-none">View All</a>
                </div>
                <div class="card-body">
                    @if($recentMessages->count() > 0)
                        @foreach($recentMessages as $message)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $message->sender->name ?? 'System' }}</strong>
                                    <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 small">{{ Str::limit($message->message, 80) }}</p>
                                @if(!$message->is_read)
                                    <span class="badge bg-primary mt-1">Unread</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No messages.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection