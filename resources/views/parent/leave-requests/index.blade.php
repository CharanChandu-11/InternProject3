{{-- resources/views/parent/leave-requests/index.blade.php --}}
@extends('layouts.parent')

@section('title', 'Leave Requests')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-umbrella-beach me-2"></i> Leave Requests
            <div class="float-end">
                <a href="{{ route('parent.leave-requests.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Apply for Leave
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="student_id" class="form-select">
                            <option value="">All Children</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}" {{ request('student_id') == $child->id ? 'selected' : '' }}>
                                    {{ $child->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('parent.leave-requests.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            @if($leaveRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Student</th>
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
                                    {{ $leave->student->user->name }}
                                 </span></td>
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
                                    <a href="{{ route('parent.leave-requests.show', $leave) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($leave->status == 'pending')
                                        <a href="{{ route('parent.leave-requests.edit', $leave) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('parent.leave-requests.cancel', $leave) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-warning delete-btn">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
                                 </span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </div>
                </div>
                {{ $leaveRequests->links() }}
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No leave requests found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection