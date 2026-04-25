{{-- resources/views/teacher/classes.blade.php --}}
@extends('layouts.teacher')

@section('title', 'My Classes')

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Classes</h6>
                            <h2 class="mb-0">{{ $stats['total_classes'] }}</h2>
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
                            <h2 class="mb-0">{{ number_format($stats['total_students']) }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Subjects Taught</h6>
                            <h2 class="mb-0">{{ number_format($stats['total_subjects']) }}</h2>
                        </div>
                        <i class="fas fa-book-open fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Avg. Class Size</h6>
                            <h2 class="mb-0">{{ $stats['average_class_size'] }}</h2>
                        </div>
                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes List -->
    <div class="row">
        @forelse($classes as $classData)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-chalkboard-user me-2 text-primary"></i>
                                {{ $classData['class_name'] }} - Section {{ $classData['section_name'] }}
                            </h5>
                            <span class="badge bg-primary">{{ $classData['students_count'] }} Students</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Attendance Summary -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-calendar-check me-1"></i> Today's Attendance
                            </h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="text-success fs-4">{{ $classData['attendance_stats']['present'] }}</div>
                                        <small>Present</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="text-danger fs-4">{{ $classData['attendance_stats']['absent'] }}</div>
                                        <small>Absent</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="text-warning fs-4">{{ $classData['attendance_stats']['late'] }}</div>
                                        <small>Late</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $classData['attendance_stats']['percentage'] }}%"></div>
                                </div>
                                <small class="text-muted">{{ $classData['attendance_stats']['percentage'] }}% attendance today</small>
                            </div>
                        </div>
                        
                        <!-- Subjects -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-book me-1"></i> Subjects Taught
                            </h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($classData['subjects'] as $subject)
                                    <span class="badge bg-info">{{ $subject->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="mt-3">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('teacher.attendance.mark', ['class' => $classData['class_id'], 'section' => $classData['section_id']]) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-calendar-check me-1"></i> Mark Attendance
                                </a>
                                <a href="{{ route('teacher.students') }}?class_id={{ $classData['class_id'] }}&section_id={{ $classData['section_id'] }}" 
                                   class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-users me-1"></i> View Students
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" 
                                        data-bs-target="#timetableModal-{{ $classData['class_id'] }}_{{ $classData['section_id'] }}">
                                    <i class="fas fa-clock me-1"></i> Timetable
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timetable Modal for each class -->
            <div class="modal fade" id="timetableModal-{{ $classData['class_id'] }}_{{ $classData['section_id'] }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-clock me-2"></i> 
                                Timetable: {{ $classData['class_name'] }} - Section {{ $classData['section_name'] }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th>Time Slot</th>
                                            <th>Subject</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                                        @endphp
                                        @foreach($days as $day)
                                            @if(isset($classData['timetable'][$day]) && $classData['timetable'][$day]->count() > 0)
                                                @foreach($classData['timetable'][$day] as $entry)
                                                    <tr>
                                                        <td rowspan="{{ $classData['timetable'][$day]->count() }}" class="bg-light">
                                                            {{ ucfirst($day) }}
                                                        </td>
                                                        <td>{{ $entry->timeSlot->time_range }}</td>
                                                        <td><strong>{{ $entry->subject->name }}</strong> {{ $entry->subject->code }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No classes assigned to you yet.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .progress {
        border-radius: 10px;
    }
    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }
    .btn-group .btn {
        font-size: 13px;
    }
    .table th, .table td {
        padding: 10px;
        vertical-align: middle;
    }
</style>
@endpush