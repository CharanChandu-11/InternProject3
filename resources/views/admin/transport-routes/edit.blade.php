{{-- resources/views/admin/transport-routes/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Transport Route')

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Route: {{ $transportRoute->route_name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.transport-routes.update', $transportRoute) }}" method="POST">
                @csrf @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Route Number <span class="text-danger">*</span></label>
                        <input type="text" name="route_number" class="form-control" value="{{ old('route_number', $transportRoute->route_number) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Route Name <span class="text-danger">*</span></label>
                        <input type="text" name="route_name" class="form-control" value="{{ old('route_name', $transportRoute->route_name) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $transportRoute->description) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Assign Vehicles</label>
                    <select name="vehicles[]" class="form-select select2" multiple>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ $transportRoute->vehicles->contains($vehicle->id) ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_number }} - {{ $vehicle->model }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i> Route Stops</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="stopsTable">
                        <thead class="table-light">
                            <tr><th>Stop Name</th><th>Pickup Time</th><th>Drop Time</th><th>Fee (₹)</th><th>Distance (km)</th><th>Action</th></tr></thead>
                        <tbody id="stopsContainer">
                            @foreach($transportRoute->stops as $index => $stop)
                            <tr class="stop-row">
                                <td><input type="text" name="stops[{{ $index }}][stop_name]" class="form-control" value="{{ $stop->stop_name }}" required></td>
                                <td><input type="time" name="stops[{{ $index }}][pickup_time]" class="form-control" value="{{ \Carbon\Carbon::parse($stop->pickup_time)->format('H:i') }}" required></td>
                                <td><input type="time" name="stops[{{ $index }}][drop_time]" class="form-control" value="{{ \Carbon\Carbon::parse($stop->drop_time)->format('H:i') }}" required></td>
                                <td><input type="number" step="0.01" name="stops[{{ $index }}][fee]" class="form-control" value="{{ $stop->fee }}" required></td>
                                <td><input type="number" step="0.01" name="stops[{{ $index }}][distance]" class="form-control" value="{{ $stop->distance_from_previous }}" placeholder="km"></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-stop"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" id="addStop" class="btn btn-secondary btn-sm mb-4"><i class="fas fa-plus-circle"></i> Add Stop</button>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update Route</button>
                    <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let stopIndex = {{ $transportRoute->stops->count() }};
    function addStopRow() {
        let newRow = `
            <tr class="stop-row">
                <td><input type="text" name="stops[${stopIndex}][stop_name]" class="form-control" required></td>
                <td><input type="time" name="stops[${stopIndex}][pickup_time]" class="form-control" required></td>
                <td><input type="time" name="stops[${stopIndex}][drop_time]" class="form-control" required></td>
                <td><input type="number" step="0.01" name="stops[${stopIndex}][fee]" class="form-control" required></td>
                <td><input type="number" step="0.01" name="stops[${stopIndex}][distance]" class="form-control" placeholder="km"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-stop"><i class="fas fa-trash"></i></button></td>
            </table>`;
        $('#stopsContainer').append(newRow);
        stopIndex++;
    }
    $(document).on('click', '#addStop', addStopRow);
    $(document).on('click', '.remove-stop', function(){
        if($('.stop-row').length > 1) $(this).closest('tr').remove();
        else alert('At least one stop required.');
        $('#stopsContainer tr').each(function(idx, row){
            $(row).find('input').each(function(){
                let name = $(this).attr('name');
                if(name) $(this).attr('name', name.replace(/stops\[\d+\]/, `stops[${idx}]`));
            });
        });
        stopIndex = $('.stop-row').length;
    });
</script>
@endpush