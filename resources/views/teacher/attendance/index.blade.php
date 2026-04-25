{{-- resources/views/teacher/attendance/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Attendance Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-check me-2"></i> Attendance Management
            <div class="float-end">
                <a href="{{ route('teacher.attendance.history') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-history me-1"></i> History
                </a>
                <a href="{{ route('teacher.attendance.summary') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-chart-bar me-1"></i> Summary
                </a>
            </div>
        </div>
        <div class="card-body">
            <h5>Select a class to mark attendance</h5>
            <div class="row mt-3">
                @forelse($classSections as $classSection)
                    <div class="col-md-4 mb-3">
                        <div class="card {{ $classSection['attendance_marked_today'] ? 'border-success' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1">
                                            {{ $classSection['class_name'] }} - Section {{ $classSection['section_name'] }}
                                        </h5>
                                        <p class="card-text text-muted small">
                                            <i class="fas fa-users me-1"></i> {{ $classSection['students_count'] }} Students
                                        </p>
                                    </div>
                                    @if($classSection['attendance_marked_today'])
                                        <span class="badge bg-success">Marked Today</span>
                                    @endif
                                </div>
                                <a href="{{ route('teacher.attendance.mark', ['class' => $classSection['class_id'], 'section' => $classSection['section_id']]) }}" 
                                   class="btn btn-primary btn-sm mt-2 w-100">
                                    <i class="fas fa-calendar-check me-1"></i> 
                                    {{ $classSection['attendance_marked_today'] ? 'Update Attendance' : 'Mark Attendance' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No classes assigned to you.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection