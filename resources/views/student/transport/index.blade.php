{{-- resources/views/student/transport/index.blade.php --}}
@extends('layouts.student')

@section('title', 'My Transport')

@section('content')
<div class="animate-fadeInUp">
    @if($transport)
        <div class="row">
            <!-- Transport Details Card -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-bus me-2"></i> My Transport Details
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-bus fa-4x text-primary"></i>
                            <h4 class="mt-2">{{ $transport->route->route_name }}</h4>
                            <p class="text-muted">Route Number: {{ $transport->route->route_number }}</p>
                        </div>
                        
                        <div class="info-box mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i> Pickup Stop:</strong>
                                    <p class="mb-0">{{ $transport->stop->stop_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-clock me-2 text-primary"></i> Pickup Time:</strong>
                                    <p class="mb-0">{{ $pickupTime->format('h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-box mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i> Drop Stop:</strong>
                                    <p class="mb-0">{{ $transport->stop->stop_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-clock me-2 text-primary"></i> Drop Time:</strong>
                                    <p class="mb-0">{{ $dropTime->format('h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-box mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-rupee-sign me-2 text-primary"></i> Monthly Fee:</strong>
                                    <p class="mb-0">₹ {{ number_format($monthlyFee, 2) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-calendar-alt me-2 text-primary"></i> Valid From:</strong>
                                    <p class="mb-0">{{ $transport->start_date->format('d M, Y') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($transport->end_date)
                            <div class="info-box">
                                <strong><i class="fas fa-calendar-times me-2 text-primary"></i> Valid Until:</strong>
                                <p class="mb-0">{{ $transport->end_date->format('d M, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Vehicle Details Card -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-truck me-2"></i> Vehicle Details
                    </div>
                    <div class="card-body">
                        @if($vehicle)
                            <div class="text-center mb-4">
                                <i class="fas fa-bus-alt fa-4x text-info"></i>
                                <h4 class="mt-2">{{ $vehicle->vehicle_number }}</h4>
                                <p class="text-muted">{{ ucfirst($vehicle->vehicle_type) }}</p>
                            </div>
                            
                            <div class="info-box mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-car me-2 text-info"></i> Model:</strong>
                                        <p class="mb-0">{{ $vehicle->model }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-users me-2 text-info"></i> Capacity:</strong>
                                        <p class="mb-0">{{ $vehicle->capacity }} seats</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-user me-2 text-info"></i> Driver Name:</strong>
                                        <p class="mb-0">{{ $vehicle->driver_name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-phone me-2 text-info"></i> Driver Contact:</strong>
                                        <p class="mb-0">{{ $vehicle->driver_phone }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-muted text-center">Vehicle information not available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Route Map / Stops Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-route me-2"></i> Route Stops
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($routeStops as $index => $stop)
                        <div class="timeline-item {{ $stop['name'] == $transport->stop->stop_name ? 'active' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>{{ $stop['name'] }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i> Pickup: {{ $stop['pickup_time'] }} | 
                                    Drop: {{ $stop['drop_time'] }}
                                </small>
                                @if($stop['name'] == $transport->stop->stop_name)
                                    <span class="badge bg-primary ms-2">Your Stop</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Additional Information -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> Please be at your pickup stop 10 minutes before the scheduled pickup time. 
            For any transport-related queries, contact the transport office or your driver.
        </div>
    @else
        <!-- No Transport Allocated -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-bus fa-5x text-muted mb-3"></i>
                <h4>No Transport Allocated</h4>
                <p class="text-muted">You don't have any active transport allocation.</p>
                <a href="{{ route('student.transport.routes') }}" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i> Browse Available Routes
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
    }
    .timeline-marker {
        position: absolute;
        left: -25px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #6c757d;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #6c757d;
    }
    .timeline-item.active .timeline-marker {
        background: #007bff;
        box-shadow: 0 0 0 2px #007bff;
    }
    .timeline-item:not(:last-child) .timeline-marker::after {
        content: '';
        position: absolute;
        left: 4px;
        top: 12px;
        width: 2px;
        height: calc(100% + 13px);
        background: #dee2e6;
    }
    .timeline-content {
        padding-bottom: 10px;
    }
    .timeline-item.active .timeline-content h6 {
        color: #007bff;
        font-weight: bold;
    }
</style>
@endpush