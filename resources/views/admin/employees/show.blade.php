{{-- resources/views/admin/employees/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Employee Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-tie me-2"></i> Employee Details: {{ $employee->user->name }}
            <div class="float-end">
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="{{ $employee->user->profile_photo_url }}" alt="" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff;">
                    <div class="mt-3">
                        <span class="badge bg-{{ $employee->user->is_active ? 'success' : 'danger' }} fs-6 p-2">
                            {{ $employee->user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Employee ID:</th>
                                    <td><span class="badge bg-info">{{ $employee->employee_id }}</span></td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td><strong>{{ $employee->user->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $employee->user->email }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $employee->user->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td>{{ $employee->user->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Date of Birth:</th>
                                    <td>{{ optional($employee->user->profile)->date_of_birth?->format('d M Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Gender:</th>
                                    <td>{{ ucfirst(optional($employee->user->profile)->gender ?? 'N/A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Department:</th>
                                    <td>{{ $employee->department }}</td>
                                </tr>
                                <tr>
                                    <th>Designation:</th>
                                    <td>{{ $employee->designation }}</td>
                                </tr>
                                <tr>
                                    <th>Employment Type:</th>
                                    <td>
                                        <span class="badge bg-{{ $employee->employment_type == 'full_time' ? 'success' : ($employee->employment_type == 'part_time' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $employee->employment_type)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Joining Date:</th>
                                    <td>{{ $employee->joining_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Qualification:</th>
                                    <td>{{ $employee->qualification ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Experience:</th>
                                    <td>{{ $employee->experience_years ?? '0' }} years</td>
                                </tr>
                                <tr>
                                    <th>Salary:</th>
                                    <td>₹ {{ number_format($employee->salary ?? 0, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-header">
                            <i class="fas fa-university"></i> Bank Details
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr><th>Bank Name:</th><td>{{ $employee->bank_name ?? 'N/A' }}</td></tr>
                                <tr><th>Account Number:</th><td>{{ $employee->bank_account ?? 'N/A' }}</td></tr>
                                <tr><th>IFSC Code:</th><td>{{ $employee->ifsc_code ?? 'N/A' }}</td></tr>
                                <tr><th>PAN Number:</th><td>{{ $employee->pan_number ?? 'N/A' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-header">
                            <i class="fas fa-chart-line"></i> Quick Stats
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>Attendance This Month:</label>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: {{ $attendancePercentage ?? 0 }}%"></div>
                                </div>
                                <small>{{ $attendancePercentage ?? 0 }}% Present</small>
                            </div>
                            <div class="mb-3">
                                <label>Leaves Taken This Year:</label>
                                <span class="float-end">{{ $leavesTaken ?? 0 }}/{{ $totalLeaves ?? 20 }}</span>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-warning" style="width: {{ ($leavesTaken ?? 0) / (($totalLeaves ?? 20) / 100) }}%"></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <i class="fas fa-calendar-alt"></i> Joined: {{ $employee->joining_date->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection