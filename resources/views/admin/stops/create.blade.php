@extends('layouts.admin')

@section('title', 'Add Stop - ' . $transportRoute->route_name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i> Add Stop to {{ $transportRoute->route_name }}
        </div>
        <div class="card-body">
            <form action="{{ route('admin.transport-routes.stops.store', $transportRoute) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Stop Name <span class="text-danger">*</span></label>
                        <input type="text" name="stop_name" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Pickup Time <span class="text-danger">*</span></label>
                        <input type="time" name="pickup_time" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Drop Time <span class="text-danger">*</span></label>
                        <input type="time" name="drop_time" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Fee (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="fee" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Distance from Previous Stop (km)</label>
                        <input type="number" step="0.01" name="distance_from_previous" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Latitude</label>
                        <input type="text" name="latitude" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Longitude</label>
                        <input type="text" name="longitude" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Stop</button>
                <a href="{{ route('admin.transport-routes.stops.index', $transportRoute) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection