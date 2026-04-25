{{-- resources/views/teacher/leaves/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Leave Applications')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-umbrella-beach me-2"></i> Leave Balance
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-4">
                            <h3 class="text-primary">{{ $leaveBalance['total'] }}</h3>
                            <small>Total</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning">{{ $leaveBalance['used'] }}</h3>
                            <small>Used</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success">{{ $leaveBalance['remaining'] }}</h3>
                            <small>Remaining</small>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ ($leaveBalance['used'] / $leaveBalance['total']) * 100 }}%"></div>
                    </div>
                    <a href="{{ route('teacher.leaves.create') }}" class="btn btn-primary mt-3 w-100">
                        <i class="fas fa-plus me-2"></i> Apply for Leave
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> Leave History
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaves as $leave)
                                    <tr>
                                        <td>{{ $leave->leaveType->name }}</td>
                                        <td>
                                            {{ $leave->start_date->format('d M, Y') }} - 
                                            {{ $leave->end_date->format('d M, Y') }}
                                        </td>
                                        <td>{{ $leave->total_days }}</td>
                                        <td>{{ Str::limit($leave->reason, 50) }}</td>
                                        <td>
                                            @if($leave->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($leave->status == 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($leave->status == 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('teacher.leaves.show', $leave) }}" class="btn btn-sm btn-info">View</a>
                                            @if($leave->status == 'pending')
                                                <a href="{{ route('teacher.leaves.edit', $leave) }}" class="btn btn-sm btn-primary">Edit</a>
                                                <form action="{{ route('teacher.leaves.destroy', $leave) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this leave application?')">Cancel</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $leaves->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection