{{-- resources/views/admin/transport-routes/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Add Transport Route')

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New Transport Route</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.transport-routes.store') }}" method="POST" id="routeForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Route Number <span class="text-danger">*</span></label>
                        <input type="text" name="route_number" class="form-control @error('route_number') is-invalid @enderror" value="{{ old('route_number') }}" placeholder="e.g., R-101" required>
                        @error('route_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Route Name <span class="text-danger">*</span></label>
                        <input type="text" name="route_name" class="form-control @error('route_name') is-invalid @enderror" value="{{ old('route_name') }}" placeholder="e.g., North Campus Route" required>
                        @error('route_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" rows="2" class="form-control @error('description') is-invalid @enderror" placeholder="Optional description">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Assign Vehicles</label>
                    <select name="vehicles[]" class="form-select select2" multiple data-placeholder="Select vehicles">
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ in_array($vehicle->id, old('vehicles', [])) ? 'selected' : '' }}>
                                {{ $vehicle->vehicle_number }} - {{ $vehicle->model }} (Capacity: {{ $vehicle->capacity }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl to select multiple</small>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i> Route Stops</h5>
                <p class="text-muted small">Define stops in order. The first stop will have no previous distance.</p>

                <div class="table-responsive">
                    <table class="table table-bordered" id="stopsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Stop Name</th>
                                <th>Pickup Time</th>
                                <th>Drop Time</th>
                                <th>Fee (₹)</th>
                                <th>Distance from Prev (km)</th>
                                <th style="width: 60px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="stopsContainer">
                            @if(old('stops'))
                                @foreach(old('stops') as $index => $stop)
                                <tr class="stop-row">
                                    <td><input type="text" name="stops[{{ $index }}][stop_name]" class="form-control" value="{{ $stop['stop_name'] }}" required></td>
                                    <td><input type="time" name="stops[{{ $index }}][pickup_time]" class="form-control" value="{{ $stop['pickup_time'] }}" required></td>
                                    <td><input type="time" name="stops[{{ $index }}][drop_time]" class="form-control" value="{{ $stop['drop_time'] }}" required></td>
                                    <td><input type="number" step="0.01" name="stops[{{ $index }}][fee]" class="form-control" value="{{ $stop['fee'] }}" required></td>
                                    <td><input type="number" step="0.01" name="stops[{{ $index }}][distance]" class="form-control" value="{{ $stop['distance'] ?? '' }}" placeholder="km"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-stop"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr class="stop-row">
                                    <td><input type="text" name="stops[0][stop_name]" class="form-control" placeholder="e.g., Main Gate" required></td>
                                    <td><input type="time" name="stops[0][pickup_time]" class="form-control" required></td>
                                    <td><input type="time" name="stops[0][drop_time]" class="form-control" required></td>
                                    <td><input type="number" step="0.01" name="stops[0][fee]" class="form-control" placeholder="0" required></td>
                                    <td><input type="number" step="0.01" name="stops[0][distance]" class="form-control" placeholder="km"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-stop"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <button type="button" id="addStop" class="btn btn-secondary btn-sm mb-4"><i class="fas fa-plus-circle me-1"></i> Add Another Stop</button>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Save Route</button>
                    <a href="{{ route('admin.transport-routes.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-times me-2"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let stopIndex = {{ count(old('stops', [])) }};
    function addStopRow() {
        let newRow = `
            <tr class="stop-row">
                <td><input type="text" name="stops[${stopIndex}][stop_name]" class="form-control" placeholder="Stop name" required></td>
                <td><input type="time" name="stops[${stopIndex}][pickup_time]" class="form-control" required></td>
                <td><input type="time" name="stops[${stopIndex}][drop_time]" class="form-control" required></td>
                <td><input type="number" step="0.01" name="stops[${stopIndex}][fee]" class="form-control" placeholder="0" required></td>
                <td><input type="number" step="0.01" name="stops[${stopIndex}][distance]" class="form-control" placeholder="km"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-stop"><i class="fas fa-trash"></i></button></td>
            </tr>`;
        $('#stopsContainer').append(newRow);
        stopIndex++;
    }
    $(document).ready(function(){
        $('#addStop').click(addStopRow);
        $(document).on('click', '.remove-stop', function(){
            if ($('.stop-row').length > 1) $(this).closest('tr').remove();
            else alert('At least one stop required.');
            // reindex names
            $('#stopsContainer tr').each(function(idx, row){
                $(row).find('input').each(function(){
                    let name = $(this).attr('name');
                    if(name) $(this).attr('name', name.replace(/stops\[\d+\]/, `stops[${idx}]`));
                });
            });
            stopIndex = $('.stop-row').length;
        });
    });
</script>
@endpush