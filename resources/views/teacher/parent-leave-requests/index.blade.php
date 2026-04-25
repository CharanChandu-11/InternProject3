{{-- resources/views/teacher/parent-leave-requests/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Parent Leave Requests')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-umbrella-beach me-2"></i> Parent Leave Requests
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="student_id" class="form-select">
                            <option value="">All Students</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->user->name }} ({{ $student->admission_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('teacher.parent-leave-requests.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            @if($leaveRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Parent</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequests as $leave)
                            <tr>
                                <td>
                                    <img src="{{ $leave->student->user->profile_photo_url }}" alt="" 
                                         style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;" class="me-2">
                                    {{ $leave->student->user->name }}<br>
                                    <small class="text-muted">{{ $leave->student->admission_number }}</small>
                                 </span></td>
                                <td>{{ $leave->parent->name }}</span></td>
                                <td>{{ $leave->leaveType->name }}</span></td>
                                <td>
                                    {{ $leave->start_date->format('d M Y') }} - 
                                    {{ $leave->end_date->format('d M Y') }}
                                 </span></td>
                                <td>{{ $leave->total_days }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $leave->status_color }}">
                                        {{ $leave->status_text }}
                                    </span>
                                 </span></td>
                                <td>{{ $leave->created_at->format('d M Y') }}</span></td>
                                <td>
                                    <a href="{{ route('teacher.parent-leave-requests.show', $leave) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                 </span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </div>
                </div>
                {{ $leaveRequests->links() }}
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No pending leave requests found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection