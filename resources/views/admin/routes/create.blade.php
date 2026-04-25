{{-- resources/views/admin/transport/routes/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Transport Route')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus-circle me-2"></i> Add New Transport Route
        </div>
        <div class="card-body">
            <form action="{{ route('admin.transport-routes.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="route_number" class="form-label">Route Number <span class="text-danger">*</span></label>
                        <input type="text" name="route_number" id="route_number" class="form-control @error('route_number') is-invalid @enderror" value="{{ old('route_number') }}" required>
                        @error('route_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="route_name" class="form-label">Route Name <span class="text-danger">*</span></label>
                        <input type="text" name="route_name" id="route_name" class="form-control @error('route_name') is-invalid @enderror" value="{{ old('route_name') }}" required>
                        @error('route_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="vehicles" class="form-label">Assign Vehicles</label>
                    <select name="vehicles[]" id="vehicles" class="form-select" multiple>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} - {{ $vehicle->model }} (Capacity: {{ $vehicle->capacity }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl to select multiple vehicles</small>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Route
                    </button>
                    <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection