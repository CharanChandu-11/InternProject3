{{-- resources/views/super-admin/classes/students.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Students - ' . $class->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> Students of {{ $class->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.students.create') }}?class_id={{ $class->id }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Student
                </a>
                <a href="{{ route('super-admin.classes.show', $class) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Class
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="section_id" class="form-select">
                            <option value="">All Sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                    Section {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, admission number..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.classes.students', $class) }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Students Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Section</th>
                            <th>Roll No</th>
                            <th>Attendance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>{{ $student->admission_number }}</td>
                            <td>
                                <img src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}" 
                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $student->user->name }}<br>
                                <small class="text-muted">{{ $student->user->email }}</small>
                            </td>
                            <td>Section {{ $student->section->name }}</td>
                            <td>{{ $student->roll_number ?? '-' }}</td>
                            <td>{{ $student->attendance_percentage }}%</td>
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