{{-- resources/views/admin/students/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Students')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-graduation-cap me-2"></i> Student Management
            <div class="float-end">
                <a href="{{ route('admin.students.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Student
                </a>
                {{-- <a href="{{ route('admin.students.import-form') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-upload me-1"></i> Import
                </a> --}}
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, admission no..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
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
                            <td class="fw-bold">{{ $student->admission_number }}</td>
                            <td>
                                <img src="{{ $student->user->profile_photo_url }}" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $student->user->name }}</td>
                            <td>{{ $student->class->name }}</td>
                            <td>{{ $student->section->name }}</td>
                            <td>{{ $student->roll_number }}</td>
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
                                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?')">
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