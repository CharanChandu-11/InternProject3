{{-- resources/views/super-admin/employees/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Employee Details - ' . $employee->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-tie me-2"></i> Employee Details: {{ $employee->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.employees.edit', $employee) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.employees.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Section -->
                <div class="col-md-3 text-center">
                    <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4361ee;">
                    <h4 class="mt-3">{{ $employee->name }}</h4>
                    <p class="text-muted">{{ $employee->employee->employee_id ?? 'N/A' }}</p>
                    <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }} fs-6">
                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <!-- Personal Details -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-envelope me-2 text-primary"></i> Email</h6>
                                <p>{{ $employee->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-phone me-2 text-primary"></i> Phone</h6>
                                <p>{{ $employee->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Date of Birth</h6>
                                <p>{{ $employee->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-venus-mars me-2 text-primary"></i> Gender</h6>
                                <p>{{ ucfirst($employee->profile?->gender ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-box">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address</h6>
                                <p>{{ $employee->address ?? 'N/A' }}</p>
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
                            <strong>Department:</strong> {{ $employee->employee->department ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Designation:</strong> {{ $employee->employee->designation ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Employment Type:</strong> {{ ucfirst(str_replace('_', ' ', $employee->employee->employment_type ?? 'N/A')) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Joining Date:</strong> {{ $employee->employee->joining_date->format('d-m-Y') ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Qualification:</strong> {{ $employee->profile?->qualification ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Experience:</strong> {{ $employee->profile?->experience_years ?? 0 }} years
                        </div>
                        <div class="col-md-3">
                            <strong>Salary:</strong> ₹ {{ number_format($employee->employee->salary ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bank Details -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Bank Details</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Bank Name:</strong> {{ $employee->employee->bank_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Account Number:</strong> {{ $employee->employee->bank_account ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>IFSC Code:</strong> {{ $employee->employee->ifsc_code ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>PAN Number:</strong> {{ $employee->employee->pan_number ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Emergency Contact -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Emergency Contact</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Contact Name:</strong> {{ $employee->profile?->emergency_contact_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Contact Number:</strong> {{ $employee->profile?->emergency_contact ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Summary -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Attendance</h6>
                            <h3>{{ $attendanceSummary['percentage'] }}%</h3>
                            <small>{{ $attendanceSummary['present'] }} / {{ $attendanceSummary['total_days'] }} days present</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Leaves</h6>
                            <h3>{{ $leaveSummary['approved'] }}</h3>
                            <small>Approved / {{ $leaveSummary['total_leaves'] }} total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Pending Leaves</h6>
                            <h3 class="text-warning">{{ $leaveSummary['pending'] }}</h3>
                            <small>Awaiting approval</small>
                        </div>
                    </div>
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