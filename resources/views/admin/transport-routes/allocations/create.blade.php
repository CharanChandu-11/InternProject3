@extends('layouts.admin')

@section('title', 'Allocate Student - ' . $transportRoute->route_name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> Allocate Student to Route: {{ $transportRoute->route_name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.transport-routes.allocations.store', $transportRoute) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">-- Choose Student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->user->name }} ({{ $student->admission_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Select Stop <span class="text-danger">*</span></label>
                        <select name="stop_id" class="form-select @error('stop_id') is-invalid @enderror" required>
                            <option value="">-- Choose Stop --</option>
                            @foreach($stops as $stop)
                                <option value="{{ $stop->id }}" {{ old('stop_id') == $stop->id ? 'selected' : '' }}>
                                    {{ $stop->stop_name }} (Pickup: {{ \Carbon\Carbon::parse($stop->pickup_time)->format('h:i A') }})
                                </option>
                            @endforeach
                        </select>
                        @error('stop_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', now()->toDateString()) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">End Date (Optional)</label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Allocate</button>
                    <a href="{{ route('admin.transport-routes.show', $transportRoute) }}" class="btn btn-outline-secondary"><i class="fas fa-times me-2"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection