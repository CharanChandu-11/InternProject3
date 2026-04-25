{{-- resources/views/super-admin/teachers/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Teacher Details - ' . $teacher->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chalkboard-user me-2"></i> Teacher Details: {{ $teacher->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.teachers.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Section -->
                <div class="col-md-3 text-center">
                    <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4361ee;">
                    <h4 class="mt-3">{{ $teacher->name }}</h4>
                    <p class="text-muted">{{ $teacher->employee->employee_id ?? 'N/A' }}</p>
                    <span class="badge bg-{{ $teacher->is_active ? 'success' : 'danger' }} fs-6">
                        {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <!-- Personal Details -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-envelope me-2 text-primary"></i> Email</h6>
                                <p>{{ $teacher->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-phone me-2 text-primary"></i> Phone</h6>
                                <p>{{ $teacher->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Date of Birth</h6>
                                <p>{{ $teacher->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-venus-mars me-2 text-primary"></i> Gender</h6>
                                <p>{{ ucfirst($teacher->profile?->gender ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-box">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address</h6>
                                <p>{{ $teacher->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Professional Details -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Professional Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Department:</strong> {{ $teacher->employee->department ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Designation:</strong> {{ $teacher->employee->designation ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Employment Type:</strong> {{ ucfirst(str_replace('_', ' ', $teacher->employee->employment_type ?? 'N/A')) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Joining Date:</strong> {{ $teacher->employee->joining_date->format('d-m-Y') ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Qualification:</strong> {{ $teacher->profile?->qualification ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Experience:</strong> {{ $teacher->profile?->experience_years ?? 0 }} years
                        </div>
                        <div class="col-md-3">
                            <strong>Salary:</strong> ₹ {{ number_format($teacher->employee->salary ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Assigned Classes -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Assigned Classes & Subjects</h5>
                    @if($assignedClasses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Class</th><th>Subjects</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($assignedClasses as $classId => $data)
                                    <tr>
                                        <td><strong>{{ $data['class']->name }}</strong> ({{ $data['class']->sections->pluck('name')->implode(', ') }})</td>
                                        <td>
                                            @foreach($data['subjects'] as $subject)
                                                <span class="badge bg-info me-1">{{ $subject->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <form action="{{ route('super-admin.teachers.remove-class', $data['subjects']->first()->pivot->id ?? 0) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No classes assigned yet.</p>
                    @endif
                </div>
            </div>
            
            <!-- Assign New Class Form -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Assign New Class</h5>
                    <form action="{{ route('super-admin.teachers.assign-class', $teacher) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                @foreach($availableClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="subject_id" class="form-select" required>
                                <option value="">Select Subject</option>
                                @foreach($availableSubjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="theory_marks" class="form-control" placeholder="Theory Marks" value="100">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Assign</button>
                        </div>
                    </form>
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
        padding: 12px;
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