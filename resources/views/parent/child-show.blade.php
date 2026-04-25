{{-- resources/views/parent/child-show.blade.php --}}
@extends('layouts.parent')

@section('title', $student->user->name . ' - Profile')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--primary);">
                    <h4>{{ $student->user->name }}</h4>
                    <p class="text-muted">{{ $student->user->email }}</p>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Admission No</small>
                                <h6 class="mb-0">{{ $student->admission_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Roll Number</small>
                                <h6 class="mb-0">{{ $student->roll_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Class</small>
                                <h6 class="mb-0">{{ $student->class->name }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Section</small>
                                <h6 class="mb-0">{{ $student->section->name }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Date of Birth</small>
                                <h6 class="mb-0">{{ $student->user->profile?->date_of_birth?->format('d M, Y') ?? 'N/A' }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Blood Group</small>
                                <h6 class="mb-0">{{ $student->user->profile?->blood_group ?? 'N/A' }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-graduation-cap me-2"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md col-6">
                                    <a href="{{ route('parent.children.attendance', $student) }}" class="text-decoration-none">
                                        <div class="bg-light rounded-3 p-3 text-center hover-card">
                                            <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                            <div class="fw-bold">Attendance</div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md col-6">
                                    <a href="{{ route('parent.children.results', $student) }}" class="text-decoration-none">
                                        <div class="bg-light rounded-3 p-3 text-center hover-card">
                                            <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                            <div class="fw-bold">Results</div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md col-6">
                                    <a href="{{ route('parent.children.fees', $student) }}" class="text-decoration-none">
                                        <div class="bg-light rounded-3 p-3 text-center hover-card">
                                            <i class="fas fa-rupee-sign fa-2x text-warning mb-2"></i>
                                            <div class="fw-bold">Fees</div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md col-6">
                                    <a href="{{ route('parent.children.homework', $student) }}" class="text-decoration-none">
                                        <div class="bg-light rounded-3 p-3 text-center hover-card">
                                            <i class="fas fa-book fa-2x text-info mb-2"></i>
                                            <div class="fw-bold">Homework</div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md col-6">
                                    <a href="{{ route('parent.children.timetable', $student) }}" class="text-decoration-none">
                                        <div class="bg-light rounded-3 p-3 text-center hover-card">
                                            <i class="fas fa-book fa-2x text-info mb-2"></i>
                                            <div class="fw-bold">Timetable</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-clock me-2"></i> Quick Stats
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Attendance Percentage</span>
                                    <span class="fw-bold">{{ $student->attendance_percentage }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $student->attendance_percentage }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Average Marks</span>
                                    <span class="fw-bold">{{ $student->average_percentage ?? 'N/A' }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $student->average_percentage ?? 0 }}%"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Fees</span>
                                <span>₹{{ number_format($student->fees()->sum('total_amount'), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Paid Fees</span>
                                <span class="text-success">₹{{ number_format($student->fees()->sum('paid_amount'), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Due Fees</span>
                                <span class="text-danger">₹{{ number_format($student->fees()->sum('due_amount'), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-clock me-2"></i> Today's Schedule
                        </div>
                        <div class="card-body">
                            @php
                                $todayTimetable = \App\Models\Timetable::where('class_id', $student->class_id)
                                    ->where('section_id', $student->section_id)
                                    ->where('day_of_week', strtolower(now()->format('l')))
                                    ->with(['subject', 'teacher', 'timeSlot'])
                                    ->get();
                            @endphp
                            @if($todayTimetable->count() > 0)
                                @foreach($todayTimetable as $class)
                                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                        <div class="bg-light rounded-3 p-2 text-center me-3" style="min-width: 80px;">
                                            <small>{{ $class->timeSlot->time_range }}</small>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $class->subject->name }}</div>
                                            <small class="text-muted">{{ $class->teacher->name }} • Room {{ $class->room_number ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center py-3">No classes scheduled for today.</p>
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
    .hover-card {
        transition: all 0.3s;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        background: var(--primary) !important;
        color: white !important;
    }
    .hover-card:hover i,
    .hover-card:hover div {
        color: white !important;
    }
</style>
@endpush