{{-- resources/views/teacher/students.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Students')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users me-2"></i> Students
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
                                <a href="{{ route('teacher.students') }}" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Attendance %</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr>
                                        <td class="fw-bold">{{ $student->roll_number }}</td>
                                        <td>
                                            <img src="{{ $student->user->profile_photo_url }}" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        </td>
                                        <td>{{ $student->user->name }}</td>
                                        <td>{{ $student->admission_number }}</td>
                                        <td>{{ $student->class->name }}</td>
                                        <td>{{ $student->section->name }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-{{ $student->attendance_percentage >= 75 ? 'success' : ($student->attendance_percentage >= 60 ? 'warning' : 'danger') }}">
                                                    {{ $student->attendance_percentage }}%
                                                </span>
                                                <div class="progress flex-grow-1" style="height: 5px;">
                                                    <div class="progress-bar bg-{{ $student->attendance_percentage >= 75 ? 'success' : ($student->attendance_percentage >= 60 ? 'warning' : 'danger') }}" 
                                                         style="width: {{ $student->attendance_percentage }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('teacher.students.show', $student) }}" class="btn btn-sm btn-info">View Profile</a>
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
    </div>
</div>
@endsection