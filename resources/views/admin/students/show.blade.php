{{-- resources/views/admin/students/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Student Details - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $student->user->profile_photo_url }}" alt="" 
                         class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--primary);">
                    <h4>{{ $student->user->name }}</h4>
                    <p class="text-muted">{{ $student->user->email }}</p>
                    <p class="text-muted">{{ $student->user->phone }}</p>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Admission No</small>
                                <h6 class="mb-0">{{ $student->admission_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Roll Number</small>
                                <h6 class="mb-0">{{ $student->roll_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Class</small>
                                <h6 class="mb-0">{{ $student->class->name }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Section</small>
                                <h6 class="mb-0">{{ $student->section->name }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Date of Birth</small>
                                <h6 class="mb-0">{{ $student->user->profile?->date_of_birth?->format('d M, Y') ?? 'N/A' }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Blood Group</small>
                                <h6 class="mb-0">{{ $student->user->profile?->blood_group ?? 'N/A' }}</h6>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        @if($student->user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Parents Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-users me-2"></i> Parents/Guardians
                </div>
                <div class="card-body">
                    @if($student->parents->count() > 0)
                        @foreach($student->parents as $parent)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="fw-bold">{{ $parent->user->name }}</div>
                                <div class="small text-muted">{{ $parent->pivot->relationship }}</div>
                                <div class="small">{{ $parent->user->email }}</div>
                                <div class="small">{{ $parent->user->phone }}</div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center mb-0">No parents assigned</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Attendance Summary Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Attendance Summary
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h2 class="text-{{ $attendanceSummary['percentage'] >= 75 ? 'success' : ($attendanceSummary['percentage'] >= 60 ? 'warning' : 'danger') }}">
                                    {{ $attendanceSummary['percentage'] }}%
                                </h2>
                                <p>Overall Attendance</p>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $attendanceSummary['percentage'] >= 75 ? 'success' : ($attendanceSummary['percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $attendanceSummary['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-6 mb-2">
                                    <div class="bg-light rounded-3 p-2">
                                        <h4 class="text-success">{{ $attendanceSummary['present'] }}</h4>
                                        <small>Present Days</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-2">
                                    <div class="bg-light rounded-3 p-2">
                                        <h4 class="text-danger">{{ $attendanceSummary['absent'] }}</h4>
                                        <small>Absent Days</small>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="bg-light rounded-3 p-2">
                                        <h4>{{ $attendanceSummary['total_days'] }}</h4>
                                        <small>Total Days</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Fee Summary Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-rupee-sign me-2"></i> Fee Summary
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 text-center">
                                <h5 class="mb-0">₹{{ number_format($feeSummary['total_fees'], 2) }}</h5>
                                <small class="text-muted">Total Fees</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 text-center">
                                <h5 class="mb-0 text-success">₹{{ number_format($feeSummary['paid'], 2) }}</h5>
                                <small class="text-muted">Paid Amount</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 text-center">
                                <h5 class="mb-0 text-danger">₹{{ number_format($feeSummary['due'], 2) }}</h5>
                                <small class="text-muted">Due Amount</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i> Edit Student
                        </a>
                        <a href="#" class="btn btn-info" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Print Profile
                        </a>
                        <a href="#" class="btn btn-success">
                            <i class="fas fa-id-card me-2"></i> Generate ID Card
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .sidebar, .topbar, .card-header .btn, .btn, .navbar, .alert {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>
@endpush