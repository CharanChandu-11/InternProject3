{{-- resources/views/admin/hostels/show.blade.php --}}
@extends('layouts.admin')

@section('title', $hostel->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-hotel me-2"></i> Hostel Details: {{ $hostel->name }}
            <div class="float-end">
                <a href="{{ route('admin.hostels.edit', $hostel) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.hostel-rooms.create') }}?hostel_id={{ $hostel->id }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> Add Room
                </a>
                <a href="{{ route('admin.hostels.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-card p-3 bg-light rounded">
                        <h6 class="text-muted mb-3">Hostel Information</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 140px;">Hostel Name:</th>
                                <td>{{ $hostel->name }}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    <span class="badge bg-{{ $hostel->type == 'boys' ? 'primary' : ($hostel->type == 'girls' ? 'danger' : 'info') }}">
                                        {{ ucfirst(str_replace('_', ' ', $hostel->type)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Warden Name:</th>
                                <td>{{ $hostel->warden_name }}</td>
                            </tr>
                            <tr>
                                <th>Warden Phone:</th>
                                <td>{{ $hostel->warden_phone }}</td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td>{{ $hostel->address }}</td>
                            </tr>
                            <tr>
                                <th>Total Rooms:</th>
                                <td>{{ $hostel->rooms->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card p-3 bg-primary text-white rounded">
                        <h6 class="mb-3">Capacity Overview</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="mb-0">{{ $totalSeats }}</h3>
                                <small>Total Seats</small>
                            </div>
                            <div class="col-4">
                                <h3 class="mb-0">{{ $occupiedSeats }}</h3>
                                <small>Occupied</small>
                            </div>
                            <div class="col-4">
                                <h3 class="mb-0">{{ $availableSeats }}</h3>
                                <small>Available</small>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress bg-white" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($occupiedSeats / max($totalSeats, 1)) * 100 }}%"></div>
                            </div>
                            <small class="mt-1 d-block">{{ round(($occupiedSeats / max($totalSeats, 1)) * 100) }}% Occupied</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <h5 class="mb-3">Rooms in this Hostel</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
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
                        @forelse($hostel->rooms as $room)
                        <tr>
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
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No rooms added yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection