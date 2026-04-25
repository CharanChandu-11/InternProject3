{{-- resources/views/admin/transport/vehicles/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Vehicle Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bus me-2"></i> Vehicle Details: {{ $vehicle->vehicle_number }}
            <div class="float-end">
                <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Vehicle Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Vehicle Number</th>
                            <td>{{ $vehicle->vehicle_number }}</td>
                        </tr>
                        <tr>
                            <th>Vehicle Type</th>
                            <td>{{ $vehicle->vehicle_type }}</td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td>{{ $vehicle->model }}</td>
                        </tr>
                        <tr>
                            <th>Capacity</th>
                            <td>{{ $vehicle->capacity }} seats</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $vehicle->created_at->format('F j, Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Driver Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Driver Name</th>
                            <td>{{ $vehicle->driver_name }}</td>
                        </tr>
                        <tr>
                            <th>License Number</th>
                            <td>{{ $vehicle->driver_license }}</td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ $vehicle->driver_phone }}</td>
                        </tr>
                        <tr>
                            <th>Insurance Expiry</th>
                            <td>
                                <span class="badge bg-{{ \Carbon\Carbon::parse($vehicle->insurance_expiry)->isPast() ? 'danger' : 'success' }}">
                                    {{ \Carbon\Carbon::parse($vehicle->insurance_expiry)->format('F j, Y') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Assigned Routes</h5>
                @if($vehicle->routes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Route Number</th>
                                    <th>Route Name</th>
                                    <th>Stops</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicle->routes as $route)
                                <tr>
                                    <td>{{ $route->route_number }}</td>
                                    <td>{{ $route->route_name }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $route->stops->count() }} stops</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">This vehicle is not assigned to any route.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection