{{-- resources/views/admin/vehicles/form.blade.php --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Vehicle Number <span class="text-danger">*</span></label>
        <input type="text" name="vehicle_number" class="form-control @error('vehicle_number') is-invalid @enderror" value="{{ old('vehicle_number', $vehicle->vehicle_number ?? '') }}" placeholder="e.g., KA-01-AB-1234" required>
        @error('vehicle_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Vehicle Type <span class="text-danger">*</span></label>
        <select name="vehicle_type" class="form-select @error('vehicle_type') is-invalid @enderror" required>
            <option value="">Select Type</option>
            <option value="bus" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'bus' ? 'selected' : '' }}>Bus</option>
            <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'van' ? 'selected' : '' }}>Van</option>
            <option value="mini_bus" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'mini_bus' ? 'selected' : '' }}>Mini Bus</option>
            <option value="car" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'car' ? 'selected' : '' }}>Car</option>
        </select>
        @error('vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
        <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" value="{{ old('model', $vehicle->model ?? '') }}" placeholder="e.g., Tata Starbus" required>
        @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Capacity (seats) <span class="text-danger">*</span></label>
        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', $vehicle->capacity ?? '') }}" min="1" required>
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Driver Name <span class="text-danger">*</span></label>
        <input type="text" name="driver_name" class="form-control @error('driver_name') is-invalid @enderror" value="{{ old('driver_name', $vehicle->driver_name ?? '') }}" required>
        @error('driver_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Driver License Number <span class="text-danger">*</span></label>
        <input type="text" name="driver_license" class="form-control @error('driver_license') is-invalid @enderror" value="{{ old('driver_license', $vehicle->driver_license ?? '') }}" required>
        @error('driver_license')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Driver Phone <span class="text-danger">*</span></label>
        <input type="text" name="driver_phone" class="form-control @error('driver_phone') is-invalid @enderror" value="{{ old('driver_phone', $vehicle->driver_phone ?? '') }}" required>
        @error('driver_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold">Insurance Expiry Date</label>
        <input type="date" name="insurance_expiry" class="form-control @error('insurance_expiry') is-invalid @enderror" value="{{ old('insurance_expiry', $vehicle->insurance_expiry ?? '') }}">
        @error('insurance_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>