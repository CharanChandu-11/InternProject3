{{-- resources/views/student/transport/routes.blade.php --}}
@extends('layouts.student')

@section('title', 'Transport Routes')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-route me-2"></i> Available Transport Routes
            <div class="float-end">
                <a href="{{ route('student.transport') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to My Transport
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by route name or number..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="stop_id" class="form-select">
                            <option value="">All Stops</option>
                            @foreach($stops as $stop)
                                <option value="{{ $stop->id }}" {{ request('stop_id') == $stop->id ? 'selected' : '' }}>
                                    {{ $stop->stop_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('student.transport.routes') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            @if($currentTransport)
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    You already have an active transport allocation on <strong>{{ $currentTransport->route->route_name }}</strong>.
                    Contact transport office to change your route.
                </div>
            @endif
            
            <!-- Routes List -->
            <div class="row">
                @forelse($routes as $route)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $route->route_name }}</h6>
                                    <span class="badge bg-primary">{{ $route->route_number }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">{{ $route->description ?? 'No description available.' }}</p>
                                
                                <div class="mb-3">
                                    <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i> Stops:</strong>
                                    <ul class="list-unstyled mt-2">
                                        @foreach($route->stops->take(3) as $stop)
                                            <li class="mb-1">
                                                <i class="fas fa-circle small me-2 text-muted"></i>
                                                {{ $stop->stop_name }}
                                                <small class="text-muted">(₹ {{ number_format($stop->fee, 2) }}/month)</small>
                                            </li>
                                        @endforeach
                                        @if($route->stops->count() > 3)
                                            <li class="text-muted small">+ {{ $route->stops->count() - 3 }} more stops</li>
                                        @endif
                                    </ul>
                                </div>
                                
                                @if($route->vehicles->first())
                                    <div class="small text-muted">
                                        <i class="fas fa-truck me-1"></i> 
                                        Vehicle: {{ $route->vehicles->first()->vehicle_number }}
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-white">
                                <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" 
                                        data-bs-target="#routeModal{{ $route->id }}">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Route Details Modal -->
                    <div class="modal fade" id="routeModal{{ $route->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $route->route_name }} ({{ $route->route_number }})</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <p>{{ $route->description ?? 'No description available.' }}</p>
                                        </div>
                                        <div class="col-md-12">
                                            <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Route Stops</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Stop Name</th>
                                                            <th>Pickup Time</th>
                                                            <th>Drop Time</th>
                                                            <th>Monthly Fee</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($route->stops as $stop)
                                                        <tr>
                                                            <td>{{ $stop->stop_name }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($stop->pickup_time)->format('h:i A') }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($stop->drop_time)->format('h:i A') }}</td>
                                                            <td>₹ {{ number_format($stop->fee, 2) }}</td>
                                                            <td>
                                                                @if(!$currentTransport)
                                                                    <button class="btn btn-sm btn-primary request-btn" 
                                                                            data-route-id="{{ $route->id }}" 
                                                                            data-stop-id="{{ $stop->id }}"
                                                                            data-stop-name="{{ $stop->stop_name }}"
                                                                            data-fee="{{ number_format($stop->fee, 2) }}">
                                                                        Request
                                                                    </button>
                                                                @endif
                                                             </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i> No transport routes found.
                        </div>
                    </div>
                @endforelse
            </div>
            
            {{ $routes->links() }}
        </div>
    </div>
</div>

<!-- Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.transport.request') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Request Transport Allocation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="route_id" id="requestRouteId">
                    <input type="hidden" name="stop_id" id="requestStopId">
                    
                    <div class="alert alert-info">
                        <p class="mb-1"><strong>Route:</strong> <span id="requestRouteName"></span></p>
                        <p class="mb-1"><strong>Stop:</strong> <span id="requestStopName"></span></p>
                        <p class="mb-0"><strong>Monthly Fee:</strong> ₹ <span id="requestFee"></span></p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Your request will be sent for approval. You will be notified once approved.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.request-btn').click(function() {
            var routeId = $(this).data('route-id');
            var stopId = $(this).data('stop-id');
            var stopName = $(this).data('stop-name');
            var fee = $(this).data('fee');
            var routeName = $(this).closest('.modal').find('.modal-title').text();
            
            $('#requestRouteId').val(routeId);
            $('#requestStopId').val(stopId);
            $('#requestStopName').text(stopName);
            $('#requestFee').text(fee);
            $('#requestRouteName').text(routeName);
            
            $('#requestModal').modal('show');
        });
    });
</script>
@endpush