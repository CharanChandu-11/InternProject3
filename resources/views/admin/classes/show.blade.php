{{-- resources/views/admin/classes/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Class Details')

@section('content')
<div class="animate-fadeInUp">
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chalkboard me-2"></i> Class Details: {{ $class->full_name }}
            <div class="float-end">
                <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-sm btn-secondary">
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
                            <h3>{{ $class->students()->count() }}</h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success text-white">
                        <div class="stat-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $class->sections->count() }}</h3>
                            <p>Sections</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-info text-white">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $class->subjects->count() }}</h3>
                            <p>Subjects</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning text-white">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>
                                @php
                                    $students = $class->students;
                                    $totalAttendance = 0;
                                    foreach($students as $student) {
                                        $totalAttendance += $student->attendance_percentage;
                                    }
                                    $avgAttendance = $students->count() > 0 ? round($totalAttendance / $students->count(), 1) : 0;
                                @endphp
                                {{ $avgAttendance }}%
                            </h3>
                            <p>Avg Attendance</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Class Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <i class="fas fa-info-circle me-2"></i> Class Information
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                         <tr>
                            <th width="35%">Class Name:</th>
                            <td><strong>{{ $class->full_name }}</strong></td>
                         </tr>
                         <tr>
                            <th>Numeric Name:</th>
                            <td>{{ $class->numeric_name ?? '—' }}</td>
                         </tr>
                         <tr>
                            <th>Academic Year:</th>
                            <td>{{ $class->academicYear->name }}</td>
                         </tr>
                         <tr>
                            <th>Class Teacher:</th>
                            <td>
                                @if($class->classTeacher)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $class->classTeacher->profile_photo_url }}" 
                                             class="rounded-circle me-2" width="35" height="35">
                                        <div>
                                            <div>{{ $class->classTeacher->name }}</div>
                                            <small class="text-muted">{{ $class->classTeacher->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                         </tr>
                         <tr>
                            <th>Capacity:</th>
                            <td>{{ $class->capacity ?? 'Unlimited' }} students per section</td>
                         </tr>
                         <tr>
                            <th>Total Capacity:</th>
                            <td>
                                @php
                                    $totalCapacity = $class->sections->sum('capacity');
                                    $totalStudents = $class->students()->count();
                                    $utilization = $totalCapacity > 0 ? ($totalStudents / $totalCapacity) * 100 : 0;
                                @endphp
                                @if($totalCapacity > 0)
                                    {{ $totalCapacity }} students
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $utilization >= 90 ? 'danger' : ($utilization >= 75 ? 'warning' : 'success') }}" 
                                             style="width: {{ $utilization }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($utilization, 1) }}% utilized</small>
                                @else
                                    Unlimited
                                @endif
                            </td>
                         </tr>
                     </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <i class="fas fa-chart-pie me-2"></i> Class Statistics
                </div>
                <div class="card-body">
                    <canvas id="classStatsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sections Section -->
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-layer-group me-2"></i> Sections ({{ $class->sections->count() }})
            </div>
            <a href="{{ route('admin.sections.create') }}?class_id={{ $class->id }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add Section
            </a>
        </div>
        <div class="card-body">
            @if($class->sections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                             <tr>
                                <th>Section</th>
                                <th>Capacity</th>
                                <th>Students</th>
                                <th>Utilization</th>
                                <th>Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($class->sections as $section)
                             <tr>
                                <td><strong>Section {{ $section->name }}</strong></td>
                                <td>{{ $section->capacity ?? 'Unlimited' }}</td>
                                <td>{{ $section->students()->count() }}</td>
                                <td>
                                    @if($section->capacity)
                                        @php $percent = ($section->students()->count() / $section->capacity) * 100; @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $percent >= 90 ? 'danger' : ($percent >= 75 ? 'warning' : 'success') }}" 
                                                     style="width: {{ $percent }}%"></div>
                                            </div>
                                            <span class="ms-2 small">{{ number_format($percent, 1) }}%</span>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.sections.show', $section) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                             </tr>
                            @endforeach
                        </tbody>
                     </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-layer-group fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No sections added for this class yet</p>
                    <a href="{{ route('admin.sections.create') }}?class_id={{ $class->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Add First Section
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Subjects Section -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <i class="fas fa-book-open me-2"></i> Subjects ({{ $class->subjects->count() }})
        </div>
        <div class="card-body">
            @if($class->subjects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                             <tr>
                                <th>Subject</th>
                                <th>Code</th>
                                <th>Teacher</th>
                                <th>Theory Marks</th>
                                <th>Practical Marks</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($class->subjects as $subject)
                             <tr>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->code }}</td>
                                <td>
                                    @if($subject->pivot->teacher_id)
                                        @php $teacher = \App\Models\User::find($subject->pivot->teacher_id); @endphp
                                        {{ $teacher->name ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </td>
                                <td>{{ $subject->pivot->theory_marks ?? '—' }}</td>
                                <td>{{ $subject->pivot->practical_marks ?? '—' }}</td>
                             </tr>
                            @endforeach
                        </tbody>
                     </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-book-open fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No subjects assigned to this class yet</p>
                    <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> Assign Subjects
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Students Section -->
    <div class="card">
        <div class="card-header bg-light">
            <i class="fas fa-graduation-cap me-2"></i> Students ({{ $class->students()->count() }})
        </div>
        <div class="card-body">
            @if($class->students()->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                             <tr>
                                <th>Roll No</th>
                                <th>Admission No</th>
                                <th>Photo</th>
                                <th>Student Name</th>
                                <th>Section</th>
                                <th>Attendance</th>
                                <th>Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($class->students as $student)
                             <tr>
                                <td>{{ $student->roll_number }}</td>
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
                                    @if($student->section)
                                        <span class="badge bg-primary">{{ $student->section->name }}</span>
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
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
                <div class="text-center py-4">
                    <i class="fas fa-user-graduate fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No students enrolled in this class yet</p>
                    <a href="{{ route('admin.students.create') }}?class_id={{ $class->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Student
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
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
    // Class Stats Chart
    const ctx = document.getElementById('classStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Students', 'Available Capacity'],
            datasets: [{
                data: [{{ $class->students()->count() }}, {{ max(0, ($totalCapacity ?? 0) - $class->students()->count()) }}],
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
                            let percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush