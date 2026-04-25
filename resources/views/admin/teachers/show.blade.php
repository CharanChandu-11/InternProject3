@extends('layouts.admin')

@section('title', 'Teacher Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chalkboard-user me-2"></i> Teacher Details
            <div class="float-end">
                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="{{ $teacher->profile_photo_url }}" alt="" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <h4>{{ $teacher->name }}</h4>
                    <p class="text-muted">{{ $teacher->employee?->designation ?? 'Teacher' }}</p>
                    <span class="badge bg-{{ $teacher->is_active ? 'success' : 'danger' }}">
                        {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="col-md-9">
                    <ul class="nav nav-tabs mb-3" id="teacherTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">Personal Info</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="professional-tab" data-bs-toggle="tab" data-bs-target="#professional" type="button">Professional</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button">Classes Taught</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button">Attendance</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="leaves-tab" data-bs-toggle="tab" data-bs-target="#leaves" type="button">Leaves</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="info">
                            <table class="table table-bordered">
                                <tr><th width="200">Employee ID</th><td>{{ $teacher->employee?->employee_id ?? 'N/A' }}</td></tr>
                                <tr><th>Email</th><td>{{ $teacher->email }}</td></tr>
                                <tr><th>Phone</th><td>{{ $teacher->phone }}</td></tr>
                                <tr><th>Emergency Contact</th><td>{{ $teacher->profile?->emergency_contact ?? 'N/A' }}</td></tr>
                                <tr><th>Date of Birth</th><td>{{ $teacher->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</td></tr>
                                <tr><th>Gender</th><td>{{ ucfirst($teacher->profile?->gender ?? 'N/A') }}</td></tr>
                                <tr><th>Address</th><td>{{ $teacher->address ?? 'N/A' }}</td></tr>
                            </table>
                        </div>
                        
                        <!-- Professional Tab -->
                        <div class="tab-pane fade" id="professional">
                            <table class="table table-bordered">
                                <tr><th width="200">Department</th><td>{{ $teacher->employee?->department ?? 'N/A' }}</td></tr>
                                <tr><th>Designation</th><td>{{ $teacher->employee?->designation ?? 'N/A' }}</td></tr>
                                <tr><th>Employment Type</th><td>{{ ucfirst(str_replace('_', ' ', $teacher->employee?->employment_type ?? 'N/A')) }}</td></tr>
                                <tr><th>Joining Date</th><td>{{ $teacher->employee?->joining_date?->format('F j, Y') ?? 'N/A' }}</td></tr>
                                <tr><th>Qualification</th><td>{{ $teacher->profile?->qualification ?? 'N/A' }}</td></tr>
                                <tr><th>Experience</th><td>{{ $teacher->profile?->experience_years ?? 0 }} years</td></tr>
                                <tr><th>Salary</th><td>₹ {{ number_format($teacher->employee?->salary ?? 0, 2) }}</td></tr>
                            </table>
                        </div>
                        
                        <!-- Classes Taught Tab -->
                        <div class="tab-pane fade" id="classes">
                            @if($classesTaught->count())
                                @foreach($classesTaught as $classId => $subjects)
                                    @php $class = $subjects->first()->class; @endphp
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <strong>{{ $class->name }}</strong>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr><th>Subject</th><th>Sections</th><th>Marks</th></tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($subjects as $item)
                                                    <tr>
                                                        <td>{{ $item->subject->name }}</td>
                                                        <td>{{ $class->sections->pluck('name')->implode(', ') }}</td>
                                                        <td>Theory: {{ $item->theory_marks }}, Practical: {{ $item->practical_marks }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">No classes assigned yet.</p>
                            @endif
                        </div>
                        
                        <!-- Attendance Tab -->
                        <div class="tab-pane fade" id="attendance">
                            @if($recentAttendance->count())
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Remarks</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentAttendance as $att)
                                            <tr>
                                                <td>{{ $att->attendance_date->format('Y-m-d') }}</td>
                                                <td><span class="badge bg-{{ $att->status == 'present' ? 'success' : ($att->status == 'late' ? 'warning' : 'danger') }}">{{ ucfirst($att->status) }}</span></td>
                                                <td>{{ $att->check_in_time?->format('h:i A') ?? '-' }}</td>
                                                <td>{{ $att->check_out_time?->format('h:i A') ?? '-' }}</td>
                                                <td>{{ $att->remarks ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No attendance records found.</p>
                            @endif
                        </div>
                        
                        <!-- Leaves Tab -->
                        <div class="tab-pane fade" id="leaves">
                            @if($leaveApplications->count())
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Type</th><th>Period</th><th>Days</th><th>Status</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leaveApplications as $leave)
                                            <tr>
                                                <td>{{ $leave->leaveType->name }}</td>
                                                <td>{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}</td>
                                                <td>{{ $leave->total_days }}</td>
                                                <td><span class="badge bg-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($leave->status) }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No leave applications found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection