{{-- resources/views/super-admin/students/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Student Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-graduation-cap me-2"></i> Student Management
            <div class="float-end">
                <a href="{{ route('super-admin.students.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Student
                </a>
                <a href="{{ route('super-admin.students.import-form') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-upload me-1"></i> Import
                </a>
                <a href="{{ route('super-admin.students.export', request()->query()) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-download me-1"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="section_id" class="form-select" id="sectionFilter">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, admission no..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('super-admin.students.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Students Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Roll No</th>
                            <th>Attendance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td><strong>{{ $student->admission_number }}</strong></td>
                            <td>
                                <img src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}" 
                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $student->user->name }}<br>
                                <small class="text-muted">{{ $student->user->email }}</small>
                            </td>
                            <td>{{ $student->class->name ?? 'N/A' }}</td>
                            <td>{{ $student->section->name ?? 'N/A' }}</td>
                            <td>{{ $student->roll_number ?? '-' }}</td>
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
                                <a href="{{ route('super-admin.students.show', $student) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.students.edit', $student) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('super-admin.students.id-card', $student) }}" class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="fas fa-id-card"></i>
                                </a>
                                <form action="{{ route('super-admin.students.destroy', $student) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
    // Load sections based on selected class
    $('#classFilter').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '{{ url("admin/sections/by-class") }}/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionFilter');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        var selected = '{{ request("section_id") }}' == section.id ? 'selected' : '';
                        sectionSelect.append('<option value="' + section.id + '" ' + selected + '>' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionFilter').empty().append('<option value="">All Sections</option>');
        }
    });
    
    // Trigger change on page load
    $('#classFilter').trigger('change');
</script>
@endpush