@extends('layouts.admin')
@section('title', 'Pending Hostel Requests')
@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-hourglass-half me-2"></i> Pending Allocation Requests
        <a href="{{ route('admin.hostel-allocations.index') }}" class="btn btn-sm btn-secondary float-end">All Allocations</a>
    </div>
    <div class="card-body">
        @if($pendingAllocations->count())
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Hostel</th>
                            <th>Room</th>
                            <th>Requested On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingAllocations as $alloc)
                        <tr>
                            <td>
                                {{ $alloc->student->user->name }}<br>
                                <small class="text-muted">{{ $alloc->student->admission_number }}</small>
                            </td>
                            <td>{{ $alloc->room->hostel->name }}</span></td>
                            <td>{{ $alloc->room->room_number }} ({{ ucfirst($alloc->room->room_type) }})</span></td>
                            <td>{{ $alloc->created_at->format('d M Y h:i A') }}</span></td>
                            <td>
                                <form action="{{ route('admin.hostel-allocations.approve', $alloc) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form action="{{ route('admin.hostel-allocations.reject', $alloc) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-danger reject-btn">Reject</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $pendingAllocations->links() }}
        @else
            <div class="alert alert-info">No pending requests.</div>
        @endif
    </div>
</div>
@endsection