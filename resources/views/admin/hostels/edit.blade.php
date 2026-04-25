{{-- resources/views/admin/hostels/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Hostel: ' . $hostel->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Hostel: {{ $hostel->name }}
            <div class="float-end">
                <a href="{{ route('admin.hostels.show', $hostel) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye me-1"></i> View
                </a>
                <a href="{{ route('admin.hostels.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.hostels.update', $hostel) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Hostel Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $hostel->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Hostel Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="boys" {{ old('type', $hostel->type) == 'boys' ? 'selected' : '' }}>Boys Hostel</option>
                            <option value="girls" {{ old('type', $hostel->type) == 'girls' ? 'selected' : '' }}>Girls Hostel</option>
                            <option value="co_ed" {{ old('type', $hostel->type) == 'co_ed' ? 'selected' : '' }}>Co-Ed Hostel</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="warden_name" class="form-label">Warden Name <span class="text-danger">*</span></label>
                        <input type="text" name="warden_name" id="warden_name" class="form-control @error('warden_name') is-invalid @enderror" 
                               value="{{ old('warden_name', $hostel->warden_name) }}" required>
                        @error('warden_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="warden_phone" class="form-label">Warden Phone <span class="text-danger">*</span></label>
                        <input type="text" name="warden_phone" id="warden_phone" class="form-control @error('warden_phone') is-invalid @enderror" 
                               value="{{ old('warden_phone', $hostel->warden_phone) }}" required>
                        @error('warden_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $hostel->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="total_rooms" class="form-label">Total Rooms (Optional)</label>
                        <input type="number" name="total_rooms" id="total_rooms" class="form-control @error('total_rooms') is-invalid @enderror" 
                               value="{{ old('total_rooms', $hostel->total_rooms) }}" min="0">
                        @error('total_rooms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">This field is informational only. Rooms will be managed separately.</small>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Changing hostel details will affect all associated rooms and allocations.
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Hostel
                    </button>
                    <a href="{{ route('admin.hostels.show', $hostel) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection