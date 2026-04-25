{{-- resources/views/admin/hostel-rooms/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Hostel Rooms')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bed me-2"></i> Hostel Room Management
            <div class="float-end">
                <a href="{{ route('admin.hostel-rooms.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Room
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="hostel_id" class="form-select">
                            <option value="">All Hostels</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}" {{ request('hostel_id') == $hostel->id ? 'selected' : '' }}>
                                    {{ $hostel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="room_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="single" {{ request('room_type') == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="double" {{ request('room_type') == 'double' ? 'selected' : '' }}>Double</option>
                            <option value="triple" {{ request('room_type') == 'triple' ? 'selected' : '' }}>Triple</option>
                            <option value="dormitory" {{ request('room_type') == 'dormitory' ? 'selected' : '' }}>Dormitory</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by room number..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('admin.hostel-rooms.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Hostel</th>
                            <th>Room No</th>
                            <th>Room Type</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Fee/Month</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                        <tr>
                            <td>{{ $room->hostel->name }}</td>
                            <td class="fw-bold">{{ $room->room_number }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($room->room_type) }}</span>
                            </td>
                            <td>{{ $room->capacity }}</td>
                            <td>{{ $room->occupied }}</td>
                            <td>{{ $room->capacity - $room->occupied }}</td>
                            <td>₹ {{ number_format($room->fee_per_month, 2) }}</td>
                            <td>
                                @if($room->occupied >= $room->capacity)
                                    <span class="badge bg-danger">Full</span>
                                @elseif($room->occupied > 0)
                                    <span class="badge bg-warning">Partial</span>
                                @else
                                    <span class="badge bg-success">Available</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.hostel-rooms.show', $room) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.hostel-rooms.edit', $room) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.hostel-rooms.destroy', $room) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this room?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No hostel rooms found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $rooms->links() }}
        </div>
    </div>
</div>
@endsection