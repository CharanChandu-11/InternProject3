@extends('layouts.admin')
@section('title', 'Hostel Allocations')
@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-list me-2"></i> All Allocations
        <div class="float-end">
            <a href="{{ route('admin.hostel-allocations.pending') }}" class="btn btn-sm btn-warning">Pending Requests</a>
            <a href="{{ route('admin.hostel-allocations.create') }}" class="btn btn-sm btn-primary">Direct Allocation</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Hostel</th>
                        <th>Room</th>
                        <th>Allocation Date</th>
                        <th>Leave Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allocations as $alloc)
                    <tr>
                        <td>
                            {{ $alloc->student->user->name }}<br>
                            <small class="text-muted">{{ $alloc->student->admission_number }}</small>
                        </td>
                        <td>{{ $alloc->room->hostel->name }}</span></td>
                        <td>{{ $alloc->room->room_number }} ({{ ucfirst($alloc->room->room_type) }})</span></td>
                        <td>{{ $alloc->allocation_date->format('d M Y') }}</span></td>
                        <td>{{ $alloc->leave_date ? $alloc->leave_date->format('d M Y') : '-' }}</span></td>
                        <td>
                            <span class="badge bg-{{ $alloc->status == 'active' ? 'success' : ($alloc->status == 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($alloc->status) }}
                            </span>
                        </td>
                        <td>
                            @if($alloc->status == 'pending')
                                <form action="{{ route('admin.hostel-allocations.approve', $alloc) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form action="{{ route('admin.hostel-allocations.reject', $alloc) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-danger reject-btn">Reject</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.hostel-allocations.destroy', $alloc) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $allocations->links() }}
    </div>
</div>
@endsection