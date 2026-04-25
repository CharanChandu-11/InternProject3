@extends('layouts.admin')

@section('title', 'Stops - ' . $transportRoute->route_name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-map-marker-alt me-2"></i> Stops for Route: {{ $transportRoute->route_name }}
            <div class="float-end">
                <a href="{{ route('admin.transport-routes.stops.create', $transportRoute) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Stop
                </a>
                <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Routes
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($stops->count())
                <div class="table-responsive">
                    <table class="table table-bordered sortable-table">
                        <thead>
                            <tr>
                                <th>Sort</th>
                                <th>Stop Name</th>
                                <th>Pickup Time</th>
                                <th>Drop Time</th>
                                <th>Fee (₹)</th>
                                <th>Distance from Prev (km)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-list">
                            @foreach($stops as $stop)
                            <tr> data-id="{{ $stop->id }}">
                                <td class="handle"><i class="fas fa-grip-vertical text-muted"></i></td>
                                <td>{{ $stop->stop_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($stop->pickup_time)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($stop->drop_time)->format('h:i A') }}</td>
                                <td>₹ {{ number_format($stop->fee, 2) }}</td>
                                <td>{{ $stop->distance_from_previous ?? '-' }} km</span></td>
                                <td>
                                    <a href="{{ route('admin.transport-routes.stops.edit', [$transportRoute, $stop]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.transport-routes.stops.destroy', [$transportRoute, $stop]) }}" method="POST" class="d-inline delete-form">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No stops defined for this route.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.js"></script>
<script>
    $(function() {
        $("#sortable-list").sortable({
            handle: ".handle",
            update: function(event, ui) {
                let orders = [];
                $('#sortable-list tr').each(function(index) {
                    orders.push({
                        id: $(this).data('id'),
                        sort_order: index + 1
                    });
                });
                $.ajax({
                    url: '{{ route("admin.transport-routes.stops.reorder", $transportRoute) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        stops: orders
                    },
                    success: function(response) {
                        // Optional: show success message
                    }
                });
            }
        });
    });
</script>
@endpush