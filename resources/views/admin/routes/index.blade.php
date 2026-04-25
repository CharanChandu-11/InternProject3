{{-- resources/views/admin/transport/routes/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Transport Routes')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-route me-2"></i> Transport Routes
            <div class="float-end">
                <a href="{{ route('admin.transport-routes.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Route
                </a>
                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-list me-1"></i> Vehicles
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by route name or number..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Route Number</th>
                            <th>Route Name</th>
                            <th>Stops</th>
                            <th>Vehicles</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                        <tr>
                            <td class="fw-bold">{{ $route->route_number }}</td>
                            <td>{{ $route->route_name }}</td>
                            <td>
                                <span class="badge bg-info">{{ $route->stops->count() }} stops</span>
                                <button class="btn btn-sm btn-link p-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#stops-{{ $route->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="collapse mt-2" id="stops-{{ $route->id }}">
                                    @foreach($route->stops as $stop)
                                        <div class="small text-muted">
                                            <i class="fas fa-map-marker-alt text-danger"></i> {{ $stop->stop_name }}
                                            ({{ \Carbon\Carbon::parse($stop->pickup_time)->format('h:i A') }})
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @foreach($route->vehicles as $vehicle)
                                    <span class="badge bg-success">{{ $vehicle->vehicle_number }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('admin.transport-routes.students', $route) }}" class="text-decoration-none">
                                    <span class="badge bg-primary">{{ $route->studentTransport_count ?? 0 }} students</span>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.transport-routes.show', $route) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.transport-routes.edit', $route) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.transport-routes.destroy', $route) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this route? This will also delete all stops associated with it.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No transport routes found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $routes->links() }}
        </div>
    </div>
</div>
@endsection