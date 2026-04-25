{{-- resources/views/super-admin/users/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-circle me-2"></i> User Details: {{ $user->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.users.edit', $user) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.users.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Section -->
                <div class="col-md-4 text-center">
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4361ee;">
                    <h3 class="mt-3">{{ $user->name }}</h3>
                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }} fs-6">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <div class="mt-3">
                        <p><strong>User Type:</strong> {{ ucfirst(str_replace('_', ' ', $user->user_type)) }}</p>
                        <p><strong>Username:</strong> {{ $user->username }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Details Section -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Date of Birth</h6>
                                <p>{{ $user->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-venus-mars me-2 text-primary"></i> Gender</h6>
                                <p>{{ ucfirst($user->profile?->gender ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-tint me-2 text-primary"></i> Blood Group</h6>
                                <p>{{ $user->profile?->blood_group ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-phone-alt me-2 text-primary"></i> Emergency Contact</h6>
                                <p>{{ $user->profile?->emergency_contact ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-box">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address</h6>
                                <p>{{ $user->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Student Specific Info -->
                    @if($user->user_type == 'student' && $user->student)
                        <div class="mt-3">
                            <h5 class="border-bottom pb-2">Student Information</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Admission No:</strong> {{ $user->student->admission_number }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Roll No:</strong> {{ $user->student->roll_number }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Class:</strong> {{ $user->student->class->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Section:</strong> {{ $user->student->section->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Admission Date:</strong> {{ $user->student->admission_date->format('d-m-Y') }}
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Employee Specific Info -->
                    @if($user->user_type == 'employee' && $user->employee)
                        <div class="mt-3">
                            <h5 class="border-bottom pb-2">Employee Information</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Employee ID:</strong> {{ $user->employee->employee_id }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Department:</strong> {{ $user->employee->department }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Designation:</strong> {{ $user->employee->designation }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Joining Date:</strong> {{ $user->employee->joining_date->format('d-m-Y') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="mt-4">
                <h5 class="border-bottom pb-2">Recent Activities</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Action</th><th>Module</th><th>Description</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                            <tr>
                                <td>{{ ucfirst($activity->action) }}</td>
                                <td>{{ ucfirst($activity->module) }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>{{ $activity->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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