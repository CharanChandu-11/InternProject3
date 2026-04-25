{{-- resources/views/admin/transport-routes/show.blade.php --}}
@extends('layouts.admin')

@section('title', $transportRoute->route_name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bus me-2"></i> {{ $transportRoute->route_name }} ({{ $transportRoute->route_number }})</h5>
            <div>
                <a href="{{ route('admin.transport-routes.edit', $transportRoute) }}" class="btn btn-sm btn-light"><i class="fas fa-edit"></i> Edit</a>
                <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-align-left text-primary me-2"></i> Description:</strong><br> {{ $transportRoute->description ?? 'No description provided.' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-truck text-primary me-2"></i> Vehicles Assigned:</strong><br>
                        @if($transportRoute->vehicles->count())
                            <ul class="list-unstyled">
                                @foreach($transportRoute->vehicles as $vehicle)
                                    <li><i class="fas fa-bus-alt me-1"></i> {{ $vehicle->vehicle_number }} ({{ $vehicle->model }}) - Driver: {{ $vehicle->driver_name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">No vehicles assigned</span>
                        @endif
                    </p>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="fw-bold"><i class="fas fa-map-marked-alt text-primary me-2"></i> Route Stops</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>Stop Name</th><th>Pickup Time</th><th>Drop Time</th><th>Fee (₹)</th><th>Distance from Prev (km)</th></tr></thead>
                    <tbody>
                        @foreach($transportRoute->stops as $index => $stop)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><i class="fas fa-map-marker-alt text-primary me-1"></i> {{ $stop->stop_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($stop->pickup_time)->format('h:i A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($stop->drop_time)->format('h:i A') }}</td>
                            <td>₹ {{ number_format($stop->fee, 2) }}</td>
                            <td>{{ $stop->distance_from_previous ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold"><i class="fas fa-users text-primary me-2"></i> Student Allocations</h5>
                <a href="{{ route('admin.transport-routes.allocations.create', $transportRoute) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus-circle me-1"></i> Add Allocation
                </a>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Stop</th>
                            <th>Pickup Time</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transportRoute->studentTransports()->with('student.user', 'stop')->get() as $allocation)
                        <tr>
                            <td>{{ $allocation->student->user->name ?? 'N/A' }}</td>
                            <td>{{ $allocation->student->admission_number ?? 'N/A' }}</td>
                            <td>{{ $allocation->stop->stop_name ?? 'N/A' }}</td>
                            <td>{{ optional($allocation->stop->pickup_time)->format('h:i A') ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($allocation->start_date)->format('d M Y') }}</td>
                            <td>{{ $allocation->end_date ? \Carbon\Carbon::parse($allocation->end_date)->format('d M Y') : 'Ongoing' }}</td>
                            <td>
                                <span class="badge {{ $allocation->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $allocation->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.transport-routes.allocations.destroy', $allocation) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No students allocated to this route.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.transport-routes.students', $transportRoute) }}" class="btn btn-info"><i class="fas fa-users me-2"></i> View Allocated Students</a>
                <a href="{{ route('admin.transport-routes.edit', $transportRoute) }}" class="btn btn-primary"><i class="fas fa-edit me-2"></i> Edit Route</a>
            </div>
        </div>
    </div>
</div>
@endsection