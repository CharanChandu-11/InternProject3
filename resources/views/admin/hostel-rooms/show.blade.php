{{-- resources/views/admin/hostel-rooms/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Room ' . $hostelRoom->room_number)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bed me-2"></i> Room Details: {{ $hostelRoom->room_number }}
            <div class="float-end">
                <a href="{{ route('admin.hostel-rooms.edit', $hostelRoom) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.hostel-rooms.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-card p-3 bg-light rounded">
                        <h6 class="text-muted mb-3">Room Information</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 140px;">Hostel:</th>
                                <td>{{ $hostelRoom->hostel->name }}</td>
                            </tr>
                            <tr>
                                <th>Room Number:</th>
                                <td class="fw-bold">{{ $hostelRoom->room_number }}</td>
                            </tr>
                            <tr>
                                <th>Room Type:</th>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($hostelRoom->room_type) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Capacity:</th>
                                <td>{{ $hostelRoom->capacity }} beds</td>
                            </tr>
                            <tr>
                                <th>Occupied:</th>
                                <td>{{ $hostelRoom->occupied }} students</td>
                            </tr>
                            <tr>
                                <th>Available:</th>
                                <td>{{ $availableSeats }} seats</td>
                            </tr>
                            <tr>
                                <th>Fee per Month:</th>
                                <td class="fw-bold text-success">₹ {{ number_format($hostelRoom->fee_per_month, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card p-3 bg-primary text-white rounded">
                        <h6 class="mb-3">Occupancy Status</h6>
                        <div class="text-center">
                            <h2 class="mb-0">{{ $hostelRoom->occupied }} / {{ $hostelRoom->capacity }}</h2>
                            <small>Students Occupied</small>
                        </div>
                        <div class="mt-3">
                            <div class="progress bg-white" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($hostelRoom->occupied / max($hostelRoom->capacity, 1)) * 100 }}%"></div>
                            </div>
                            <small class="mt-1 d-block">{{ round(($hostelRoom->occupied / max($hostelRoom->capacity, 1)) * 100) }}% Occupied</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <h5 class="mb-3">Current Occupants</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Allocation Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hostelRoom->allocations as $allocation)
                        <tr>
                            <td>{{ $allocation->student->user->name }}</td>
                            <td>{{ $allocation->student->admission_number }}</td>
                            <td>{{ $allocation->student->class->name ?? 'N/A' }}</td>
                            <td>{{ $allocation->student->section->name ?? 'N/A' }}</td>
                            <td>{{ $allocation->allocation_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $allocation->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($allocation->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No students currently allocated to this room.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection