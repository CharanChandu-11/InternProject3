{{-- resources/views/super-admin/sections/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Section Details - ' . $section->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-layer-group me-2"></i> Section Details: {{ $section->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.sections.edit', $section) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.sections.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-building me-2 text-primary"></i> Class</h6>
                        <p>{{ $section->class->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Academic Year</h6>
                        <p>{{ $section->class->academicYear->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-tag me-2 text-primary"></i> Section Name</h6>
                        <p><span class="badge bg-primary fs-5">Section {{ $section->name }}</span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-users me-2 text-primary"></i> Capacity</h6>
                        <p>{{ $section->capacity }} students</p>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Total Students</h6>
                            <h3>{{ $stats['total_students'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Capacity Utilization</h6>
                            <h3 class="text-{{ $stats['capacity_utilization'] >= 100 ? 'danger' : ($stats['capacity_utilization'] >= 80 ? 'warning' : 'success') }}">
                                {{ $stats['capacity_utilization'] }}%
                            </h3>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-{{ $stats['capacity_utilization'] >= 100 ? 'danger' : ($stats['capacity_utilization'] >= 80 ? 'warning' : 'success') }}" 
                                     style="width: {{ min(100, $stats['capacity_utilization']) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Boys</h6>
                            <h3 class="text-primary">{{ $stats['boys'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Girls</h6>
                            <h3 class="text-danger">{{ $stats['girls'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Students List -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Students in this Section</h5>
                    @if($students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Admission No</th>
                                        <th>Photo</th>
                                        <th>Name</th>
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
                                                 style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                                        </td>
                                        <td>{{ $student->user->name }}<br>
                                            <small class="text-muted">{{ $student->user->email }}</small>
                                        </td>
                                        <td>{{ $student->roll_number ?? '-' }}</td>
                                        <td>{{ $student->attendance_percentage }}%</td>
                                        <td>
                                            <a href="{{ route('super-admin.students.show', $student) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('super-admin.students.edit', $student) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $students->links() }}
                    @else
                        <p class="text-muted">No students in this section yet.</p>
                        <a href="{{ route('super-admin.students.create') }}?class_id={{ $section->class_id }}&section_id={{ $section->id }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Student
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .info-box h6 {
        margin-bottom: 8px;
    }
    .info-box p {
        margin-bottom: 0;
        color: #6c757d;
    }
</style>
@endpush