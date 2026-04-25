{{-- resources/views/student/transport.blade.php --}}
@extends('layouts.student')

@section('title', 'Transport')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bus"></i> My Transport Details
                    <a href="{{ route('student.transport.routes') }}" class="float-end btn btn-sm btn-outline-primary">View All Routes</a>
                </div>
                <div class="card-body">
                    @if($transport)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Route Information</h6>
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Route Name</span>
                                            <span class="fw-bold">{{ $transport->route->route_name }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="text-muted" style="width: 100px;">Route Number</span>
                                            <span class="fw-bold">{{ $transport->route->route_number }}</span>
                                        </div>
                                        @if($transport->route->description)
                                            <div class="mt-2">
                                                <small class="text-muted">{{ $transport->route->description }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Stop Information</h6>
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Stop Name</span>
                                            <span class="fw-bold">{{ $transport->stop->stop_name }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Pickup Time</span>
                                            <span class="fw-bold text-primary">{{ $transport->stop->pickup_time->format('h:i A') }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Drop Time</span>
                                            <span class="fw-bold text-primary">{{ $transport->stop->drop_time->format('h:i A') }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="text-muted" style="width: 100px;">Monthly Fee</span>
                                            <span class="fw-bold">₹{{ number_format($transport->stop->fee, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($vehicle)
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Vehicle Information</h6>
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Vehicle Number</span>
                                            <span class="fw-bold">{{ $vehicle->vehicle_number }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Vehicle Type</span>
                                            <span class="fw-bold">{{ ucfirst($vehicle->vehicle_type) }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Driver Name</span>
                                            <span class="fw-bold">{{ $vehicle->driver_name }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="text-muted" style="width: 100px;">Driver Contact</span>
                                            <span class="fw-bold">{{ $vehicle->driver_phone }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bus fa-4x text-muted mb-3"></i>
                            <h5>No Transport Allocated</h5>
                            <p class="text-muted">You haven't been assigned to any transport route yet.</p>
                            <a href="{{ route('student.transport.routes') }}" class="btn btn-primary mt-2">
                                Browse Available Routes
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection