{{-- resources/views/admin/transport/routes/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Route Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-route me-2"></i> Route Details: {{ $transportRoute->route_name }}
            <div class="float-end">
                <a href="{{ route('admin.transport-routes.edit', $transportRoute) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Route Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Route Number</th>
                            <td>{{ $transportRoute->route_number }}</td>
                        </tr>
                        <tr>
                            <th>Route Name</th>
                            <td>{{ $transportRoute->route_name }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $transportRoute->description ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $transportRoute->created_at->format('F j, Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Assigned Vehicles</h5>
                    @if($transportRoute->vehicles->count() > 0)
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Vehicle No</th>
                                    <th>Model</th>
                                    <th>Driver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transportRoute->vehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle->vehicle_number }}</td>
                                    <td>{{ $vehicle->model }}</td>
                                    <td>{{ $vehicle->driver_name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No vehicles assigned to this route.</p>
                    @endif
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Route Stops</h5>
                @if($transportRoute->stops->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Stop Name</th>
                                    <th>Pickup Time</th>
                                    <th>Drop Time</th>
                                    <th>Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transportRoute->stops as $stop)
                                <tr>
                                    <td><i class="fas fa-map-marker-alt text-danger me-2"></i>{{ $stop->stop_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($stop->pickup_time)->format('h:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($stop->drop_time)->format('h:i A') }}</td>
                                    <td>₹ {{ number_format($stop->fee, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No stops added to this route yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection