{{-- resources/views/admin/hostel-rooms/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Room: ' . $hostelRoom->room_number)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Room: {{ $hostelRoom->room_number }}
            <div class="float-end">
                <a href="{{ route('admin.hostel-rooms.show', $hostelRoom) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye me-1"></i> View
                </a>
                <a href="{{ route('admin.hostel-rooms.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hostel-rooms.update', $hostelRoom) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="hostel_id" class="form-label">Hostel <span class="text-danger">*</span></label>
                        <select name="hostel_id" id="hostel_id" class="form-select @error('hostel_id') is-invalid @enderror" required>
                            <option value="">Select Hostel</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}" {{ old('hostel_id', $hostelRoom->hostel_id) == $hostel->id ? 'selected' : '' }}>
                                    {{ $hostel->name }} ({{ ucfirst(str_replace('_', ' ', $hostel->type)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('hostel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" id="room_number" class="form-control @error('room_number') is-invalid @enderror" 
                               value="{{ old('room_number', $hostelRoom->room_number) }}" required>
                        @error('room_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="room_type" class="form-label">Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" id="room_type" class="form-select @error('room_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="single" {{ old('room_type', $hostelRoom->room_type) == 'single' ? 'selected' : '' }}>Single (1 bed)</option>
                            <option value="double" {{ old('room_type', $hostelRoom->room_type) == 'double' ? 'selected' : '' }}>Double (2 beds)</option>
                            <option value="triple" {{ old('room_type', $hostelRoom->room_type) == 'triple' ? 'selected' : '' }}>Triple (3 beds)</option>
                            <option value="dormitory" {{ old('room_type', $hostelRoom->room_type) == 'dormitory' ? 'selected' : '' }}>Dormitory (4+ beds)</option>
                        </select>
                        @error('room_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="capacity" class="form-label">Capacity (Number of Beds) <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                               value="{{ old('capacity', $hostelRoom->capacity) }}" min="{{ $hostelRoom->occupied }}" required>
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Current occupied: {{ $hostelRoom->occupied }}. Capacity cannot be less than occupied.
                        </small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="fee_per_month" class="form-label">Fee per Month (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="fee_per_month" id="fee_per_month" class="form-control @error('fee_per_month') is-invalid @enderror" 
                               value="{{ old('fee_per_month', $hostelRoom->fee_per_month) }}" min="0" step="0.01" required>
                        @error('fee_per_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Room Status:</strong>
                    @if($hostelRoom->occupied >= $hostelRoom->capacity)
                        <span class="badge bg-danger ms-2">Full</span>
                    @elseif($hostelRoom->occupied > 0)
                        <span class="badge bg-warning ms-2">Partially Occupied ({{ $hostelRoom->occupied }}/{{ $hostelRoom->capacity }})</span>
                    @else
                        <span class="badge bg-success ms-2">Available</span>
                    @endif
                </div>
                
                @if($hostelRoom->occupied > 0)
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This room currently has {{ $hostelRoom->occupied }} occupant(s). 
                    Changing capacity below occupied count is not allowed. Changing fee will affect future payments.
                </div>
                @endif
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Room
                    </button>
                    <a href="{{ route('admin.hostel-rooms.show', $hostelRoom) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    @if($hostelRoom->occupied == 0)
                    <form action="{{ route('admin.hostel-rooms.destroy', $hostelRoom) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this room?')">
                            <i class="fas fa-trash me-1"></i> Delete Room
                        </button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
    
    @if($hostelRoom->occupied > 0)
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> Current Occupants ({{ $hostelRoom->occupied }})
        </div>
        <div class="card-body">
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
                        @foreach($hostelRoom->allocations as $allocation)
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection