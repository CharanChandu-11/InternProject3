{{-- resources/views/super-admin/classes/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Class Details - ' . $class->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-building me-2"></i> Class Details: {{ $class->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.classes.edit', $class) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.classes.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-tag me-2 text-primary"></i> Class Name</h6>
                        <p>{{ $class->name }} @if($class->numeric_name) ({{ $class->numeric_name }}) @endif</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Academic Year</h6>
                        <p>{{ $class->academicYear->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-chalkboard-user me-2 text-primary"></i> Class Teacher</h6>
                        <p>
                            @if($class->classTeacher)
                                {{ $class->classTeacher->name }}<br>
                                <small class="text-muted">{{ $class->classTeacher->employee->designation ?? 'Teacher' }}</small>
                            @else
                                <span class="text-muted">Not Assigned</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-users me-2 text-primary"></i> Total Students</h6>
                        <p>{{ number_format($totalStudents) }} students</p>
                    </div>
                </div>
            </div>
            
            <!-- Sections -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Sections</h5>
                    <div class="row">
                        @foreach($class->sections as $section)
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h4>Section {{ $section->name }}</h4>
                                        <p class="mb-1">Capacity: {{ $section->capacity }}</p>
                                        <p>Students: {{ $section->students_count ?? 0 }}</p>
                                        <div class="progress">
                                            <div class="progress-bar bg-{{ ($section->students_count ?? 0) >= $section->capacity ? 'danger' : 'success' }}" 
                                                 style="width: {{ $section->capacity > 0 ? (($section->students_count ?? 0) / $section->capacity) * 100 : 0 }}%"></div>
                                        </div>
                                        <a href="{{ route('super-admin.classes.students', $class) }}?section_id={{ $section->id }}" class="btn btn-sm btn-primary mt-2">
                                            View Students
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Subjects -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Subjects</h5>
                    @if($class->subjects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Subject</th><th>Code</th><th>Teacher</th><th>Marks (Theory/Practical)</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($class->subjects as $subject)
                                    <tr>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->code }}</td>
                                        <td>{{ $subject->pivot->teacher->name ?? 'Not Assigned' }}</td>
                                        <td>{{ $subject->pivot->theory_marks ?? 0 }} / {{ $subject->pivot->practical_marks ?? 0 }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No subjects assigned yet.</p>
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