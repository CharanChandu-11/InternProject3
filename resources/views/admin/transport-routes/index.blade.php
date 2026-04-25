{{-- resources/views/admin/transport-routes/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Transport Routes')

@section('content')
<div class="animate-fadeInUp">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-gradient fw-bold"><i class="fas fa-bus me-2"></i> Transport Routes</h2>
        <a href="{{ route('admin.transport-routes.create') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Add New Route
        </a>
    </div>

    <div class="row">
        @forelse($routes as $route)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card route-card h-100 border-0 shadow-sm hover-lift">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">{{ $route->route_name }}</h5>
                        <span class="badge bg-light text-dark">{{ $route->route_number }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <strong>Stops:</strong> {{ $route->stops->count() }}
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-truck text-primary me-2"></i>
                        <strong>Vehicles:</strong> 
                        {{ $route->vehicles->pluck('vehicle_number')->implode(', ') ?: 'None assigned' }}
                    </div>
                    @if($route->description)
                        <div class="mb-3">
                            <i class="fas fa-align-left text-primary me-2"></i>
                            <strong>Description:</strong><br>
                            <small class="text-muted">{{ Str::limit($route->description, 80) }}</small>
                        </div>
                    @endif
                    <div class="progress mt-2 mb-3" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: {{ min(100, $route->stops->count() * 10) }}%"></div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.transport-routes.show', $route) }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye me-1"></i> View
                        </a>
                        <a href="{{ route('admin.transport-routes.edit', $route) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.transport-routes.students', $route) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-users me-1"></i> Students
                        </a>
                        <form action="{{ route('admin.transport-routes.destroy', $route) }}" method="POST" class="d-inline">
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
                <i class="fas fa-bus fa-3x mb-3 d-block"></i>
                <h5>No transport routes found</h5>
                <a href="{{ route('admin.transport-routes.create') }}" class="btn btn-primary mt-2">Create your first route</a>
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $routes->links() }}
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
    .route-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .route-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 35px -10px rgba(0,0,0,0.2) !important;
    }
    .hover-lift {
        transition: all 0.2s ease;
    }
</style>
@endpush