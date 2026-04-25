{{-- resources/views/admin/hostel-rooms/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Hostel Room')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus-circle me-2"></i> Add New Hostel Room
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hostel-rooms.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="hostel_id" class="form-label">Hostel <span class="text-danger">*</span></label>
                        <select name="hostel_id" id="hostel_id" class="form-select @error('hostel_id') is-invalid @enderror" required>
                            <option value="">Select Hostel</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}" {{ old('hostel_id') == $hostel->id || request('hostel_id') == $hostel->id ? 'selected' : '' }}>
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
                        <input type="text" name="room_number" id="room_number" class="form-control @error('room_number') is-invalid @enderror" value="{{ old('room_number') }}" required>
                        @error('room_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="room_type" class="form-label">Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" id="room_type" class="form-select @error('room_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="single" {{ old('room_type') == 'single' ? 'selected' : '' }}>Single (1 bed)</option>
                            <option value="double" {{ old('room_type') == 'double' ? 'selected' : '' }}>Double (2 beds)</option>
                            <option value="triple" {{ old('room_type') == 'triple' ? 'selected' : '' }}>Triple (3 beds)</option>
                            <option value="dormitory" {{ old('room_type') == 'dormitory' ? 'selected' : '' }}>Dormitory (4+ beds)</option>
                        </select>
                        @error('room_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="capacity" class="form-label">Capacity (Number of Beds) <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity') }}" min="1" required>
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="fee_per_month" class="form-label">Fee per Month (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="fee_per_month" id="fee_per_month" class="form-control @error('fee_per_month') is-invalid @enderror" value="{{ old('fee_per_month') }}" min="0" step="0.01" required>
                        @error('fee_per_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Room
                    </button>
                    <a href="{{ route('admin.hostel-rooms.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection