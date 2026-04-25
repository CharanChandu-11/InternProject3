{{-- resources/views/admin/vehicles/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Vehicles')

@section('content')
<div class="animate-fadeInUp">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-gradient fw-bold"><i class="fas fa-truck me-2"></i> Vehicle Management</h2>
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Add New Vehicle
        </a>
    </div>

    <div class="row">
        @forelse($vehicles as $vehicle)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card vehicle-card h-100 border-0 shadow-sm hover-lift">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">{{ $vehicle->vehicle_number }}</h5>
                        <span class="badge bg-light text-dark">{{ ucfirst($vehicle->vehicle_type) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-car text-primary me-2"></i>
                        <strong>Model:</strong> {{ $vehicle->model }}
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-users text-primary me-2"></i>
                        <strong>Capacity:</strong> {{ $vehicle->capacity }} seats
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-user-circle text-primary me-2"></i>
                        <strong>Driver:</strong> {{ $vehicle->driver_name }}
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-phone-alt text-primary me-2"></i>
                        <strong>Driver Phone:</strong> {{ $vehicle->driver_phone }}
                    </div>
                    @if($vehicle->insurance_expiry)
                    <div class="mb-3">
                        <i class="fas fa-file-invoice text-primary me-2"></i>
                        <strong>Insurance Expiry:</strong>
                        <span class="{{ \Carbon\Carbon::parse($vehicle->insurance_expiry)->isPast() ? 'text-danger' : 'text-success' }}">
                            {{ \Carbon\Carbon::parse($vehicle->insurance_expiry)->format('d M Y') }}
                        </span>
                    </div>
                    @endif
                    <div class="progress mt-2 mb-2" style="height: 5px;">
                        <div class="progress-bar bg-info" style="width: 75%"></div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-truck fa-3x mb-3 d-block"></i>
                <h5>No vehicles found</h5>
                <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary mt-2">Add your first vehicle</a>
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $vehicles->links() }}
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-gradient {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    }
    .vehicle-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .vehicle-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 35px -10px rgba(0,0,0,0.2) !important;
    }
    .hover-lift {
        transition: all 0.2s ease;
    }
</style>
@endpush