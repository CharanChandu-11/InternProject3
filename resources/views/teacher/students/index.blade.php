{{-- resources/views/teacher/students/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'My Students')

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
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
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Classes Taught</h6>
                            <h2 class="mb-0">{{ $stats['total_classes'] }}</h2>
                        </div>
                        <i class="fas fa-chalkboard-user fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Avg. Students/Class</h6>
                            <h2 class="mb-0">{{ $stats['average_per_class'] }}</h2>
                        </div>
                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> My Students
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="class_id" class="form-select" id="classSelect">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="section_id" class="form-select" id="sectionSelect">
                            <option value="">All Sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, admission number..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('teacher.students') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Students Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Photo</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Attendance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>{{ $student->roll_number ?? '-' }}</td>
                            <td>
                                <img src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}" 
                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>
                                {{ $student->user->name }}<br>
                                <small class="text-muted">{{ $student->user->email }}</small>
                            </td>
                            <td>{{ $student->admission_number }}</td>
                            <td>{{ $student->class->name }}</td>
                            <td>{{ $student->section->name }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $student->attendance_percentage }}%</span>
                                    <div class="progress flex-grow-1" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $student->attendance_percentage >= 75 ? 'success' : ($student->attendance_percentage >= 60 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $student->attendance_percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('teacher.students.show', $student) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Load sections when class changes
    $('#classSelect').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/teacher/sections/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionSelect');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionSelect').empty().append('<option value="">All Sections</option>');
        }
    });
</script>
@endpush