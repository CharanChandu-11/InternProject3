{{-- resources/views/admin/sections/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Section Details')

@section('content')
<div class="animate-fadeInUp">
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-layer-group me-2"></i> Section Details: {{ $section->class->full_name }} - Section {{ $section->name }}
            <div class="float-end">
                <a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.sections.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card bg-primary text-white">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $section->students()->count() }}</h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success text-white">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            @php
                                $avgAttendance = 0;
                                $students = $section->students;
                                if($students->count() > 0) {
                                    $totalAttendance = 0;
                                    foreach($students as $student) {
                                        $totalAttendance += $student->attendance_percentage;
                                    }
                                    $avgAttendance = round($totalAttendance / $students->count(), 1);
                                }
                            @endphp
                            <h3>{{ $avgAttendance }}%</h3>
                            <p>Avg Attendance</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-info text-white">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $section->class->full_name }}</h3>
                            <p>Class</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning text-white">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            @php
                                $capacity = $section->capacity;
                                $studentCount = $section->students()->count();
                                $utilization = $capacity > 0 ? round(($studentCount / $capacity) * 100, 1) : 0;
                            @endphp
                            <h3>{{ $utilization }}%</h3>
                            <p>Utilization</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <i class="fas fa-info-circle me-2"></i> Section Information
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Section Name:</th>
                            <td><strong>{{ $section->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Class:</th>
                            <td>
                                <a href="{{ route('admin.classes.show', $section->class) }}" class="text-decoration-none">
                                    {{ $section->class->full_name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Academic Year:</th>
                            <td>{{ $section->class->academicYear->name }}</td>
                        </tr>
                        <tr>
                            <th>Capacity:</th>
                            <td>{{ $section->capacity ?? 'Unlimited' }}</td>
                        </tr>
                        <tr>
                            <th>Class Teacher:</th>
                            <td>
                                @if($section->class->classTeacher)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $section->class->classTeacher->profile_photo_url }}" 
                                             class="rounded-circle me-2" width="30" height="30">
                                        {{ $section->class->classTeacher->name }}
                                    </div>
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Current Strength:</th>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $studentCount }} students</span>
                                    @if($section->capacity)
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $utilization >= 90 ? 'danger' : ($utilization >= 75 ? 'warning' : 'success') }}" 
                                                 style="width: {{ $utilization }}%"></div>
                                        </div>
                                        <small class="text-muted mt-1">
                                            {{ $capacity - $studentCount }} seats available
                                        </small>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <i class="fas fa-chart-pie me-2"></i> Section Statistics
                </div>
                <div class="card-body">
                    <canvas id="sectionStatsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Students Section -->
    <div class="card">
        <div class="card-header bg-light">
            <i class="fas fa-graduation-cap me-2"></i> Students ({{ $studentCount }})
        </div>
        <div class="card-body">
            @if($studentCount > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Roll No</th>
                                <th>Admission No</th>
                                <th>Photo</th>
                                <th>Student Name</th>
                                <th>Attendance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section->students as $student)
                            <tr>
                                <td class="fw-bold">{{ $student->roll_number }}</td>
                                <td>{{ $student->admission_number }}</td>
                                <td>
                                    <img src="{{ $student->user->profile_photo_url }}" 
                                         class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $student->user->name }}</div>
                                    <small class="text-muted">{{ $student->user->email }}</small>
                                </td>
                                <td>
                                    @php
                                        $attendancePercentage = $student->attendance_percentage;
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold">{{ number_format($attendancePercentage, 1) }}%</span>
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <div class="progress-bar bg-{{ $attendancePercentage >= 75 ? 'success' : ($attendancePercentage >= 60 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $attendancePercentage }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-graduate fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-2">No students assigned to this section yet</p>
                    <a href="{{ route('admin.students.create') }}?class_id={{ $section->class_id }}&section_id={{ $section->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Student to This Section
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }
    .stat-info h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: bold;
    }
    .stat-info p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.85rem;
    }
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Section Stats Chart
    const ctx = document.getElementById('sectionStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Students', 'Available Capacity'],
            datasets: [{
                data: [{{ $studentCount }}, {{ max(0, ($section->capacity ?? 0) - $studentCount) }}],
                backgroundColor: ['#007bff', '#e9ecef'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection