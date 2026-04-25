{{-- resources/views/student/transport-routes.blade.php --}}
@extends('layouts.student')

@section('title', 'Transport Routes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-route me-2"></i> Available Transport Routes
                <a href="{{ route('student.transport') }}" class="btn btn-sm btn-secondary float-end">My Transport</a>
            </div>
            <div class="card-body">
                @foreach($routes as $route)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">{{ $route->route_name }} (Route {{ $route->route_number }})</h5>
                        </div>
                        <div class="card-body">
                            @if($route->description)
                                <p>{{ $route->description }}</p>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Stops:</strong>
                                    <ul class="list-unstyled mt-2">
                                        @foreach($route->stops as $stop)
                                            <li class="mb-2">
                                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                {{ $stop->stop_name }}
                                                <br>
                                                <small class="text-muted ms-4">
                                                    Pickup: {{ $stop->pickup_time->format('h:i A') }} | 
                                                    Drop: {{ $stop->drop_time->format('h:i A') }} |
                                                    Fee: ₹{{ number_format($stop->fee, 2) }}/month
                                                </small>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @if($route->vehicles->first())
                                    <div class="col-md-6">
                                        <strong>Vehicle:</strong>
                                        <div class="mt-2">
                                            <p><i class="fas fa-bus me-2"></i> {{ $route->vehicles->first()->vehicle_number }}</p>
                                            <p><i class="fas fa-user me-2"></i> Driver: {{ $route->vehicles->first()->driver_name }}</p>
                                            <p><i class="fas fa-phone me-2"></i> {{ $route->vehicles->first()->driver_phone }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection