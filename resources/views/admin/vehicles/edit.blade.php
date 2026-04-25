{{-- resources/views/admin/vehicles/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Vehicle')

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Vehicle: {{ $vehicle->vehicle_number }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.vehicles.update', $vehicle) }}" method="POST">
                @csrf @method('PUT')
                @include('admin.vehicles.form', ['vehicle' => $vehicle])
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Update Vehicle</button>
                    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-times me-2"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection