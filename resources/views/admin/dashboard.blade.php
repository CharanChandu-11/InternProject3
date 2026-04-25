{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="animate-fadeInUp">
    <!-- Welcome Banner -->
    <div class="card bg-gradient-primary text-white mb-4" style="background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">Welcome, {{ Auth::user()->name }}!</h2>
                    <p class="mb-0 opacity-75">Manage school operations, track performance, and oversee all activities.</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-chalkboard-user fa-4x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="stat-number">{{ number_format($stats['total_students']) }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #06ffa5 0%, #00d4a0 100%);">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div class="stat-number">{{ number_format($stats['total_teachers']) }}</div>
                <div class="stat-label">Teachers</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number">{{ $stats['today_present'] }} / {{ $stats['today_present'] + $stats['today_absent'] }}</div>
                <div class="stat-label">Present Today</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ffd166 0%, #ffbe3c 100%);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-number">₹{{ number_format($stats['pending_fees'], 0) }}</div>
                <div class="stat-label">Pending Fees</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Students -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-graduation-cap me-2"></i> Recent Students
                    <a href="{{ route('admin.students.index') }}" class="float-end text-decoration-none small">View All →</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Admission No</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentStudents as $student)
                                <tr>
                                    <td>{{ $student->admission_number }}</td>
                                    <td>{{ $student->user->name }}</td>
                                    <td>{{ $student->class->name }}</td>
                                    <td>{{ $student->section->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Payments -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-rupee-sign me-2"></i> Recent Payments
                    <a href="{{ route('admin.payments.index') }}" class="float-end text-decoration-none small">View All →</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <td>
                                    <th>Receipt No</th>
                                    <th>Student</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_number }}</td>
                                    <td>{{ $payment->student->user->name }}</td>
                                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Events -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Upcoming Events
                    <a href="{{ route('admin.events.index') }}" class="float-end text-decoration-none small">View All →</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($upcomingEvents as $event)
                        <div class="col-md-4 mb-3">
                            <div class="event-card">
                                <div class="event-date">
                                    <div class="day">{{ $event->start_date->format('d') }}</div>
                                    <div class="month">{{ $event->start_date->format('M') }}</div>
                                </div>
                                <div class="event-info">
                                    <h6 class="mb-1">{{ $event->title }}</h6>
                                    <small class="text-muted">{{ $event->venue }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }
    
    .stat-icon i {
        font-size: 24px;
        color: white;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: var(--gray);
        font-size: 14px;
    }
    
    .event-card {
        display: flex;
        background: #f8f9fa;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s;
    }
    
    .event-card:hover {
        transform: translateX(5px);
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .event-date {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        text-align: center;
        padding: 12px;
        min-width: 70px;
    }
    
    .event-date .day {
        font-size: 24px;
        font-weight: bold;
        line-height: 1;
    }
    
    .event-date .month {
        font-size: 11px;
        text-transform: uppercase;
    }
    
    .event-info {
        padding: 12px;
        flex: 1;
    }
</style>
@endpush