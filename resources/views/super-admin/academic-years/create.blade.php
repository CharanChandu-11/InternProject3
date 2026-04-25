{{-- resources/views/super-admin/academic-years/create.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Add Academic Year')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i> Add Academic Year
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.academic-years.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Academic Year Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               placeholder="e.g., 2024-2025" value="{{ old('name') }}" required>
                        <small class="text-muted">Format: YYYY-YYYY (e.g., 2024-2025)</small>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="is_current" class="form-check-input" value="1" 
                                   {{ old('is_current') ? 'checked' : '' }}>
                            <label class="form-check-label">Set as Current Academic Year</label>
                        </div>
                        <small class="text-muted">If checked, this will be set as the current active academic year.</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                               value="{{ old('start_date') }}" required>
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                               value="{{ old('end_date') }}" required>
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> The academic year duration will be calculated automatically.
                    @if(old('start_date') && old('end_date'))
                        Duration: {{ \Carbon\Carbon::parse(old('start_date'))->diffInDays(\Carbon\Carbon::parse(old('end_date'))) }} days
                    @endif
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Academic Year
                    </button>
                    <a href="{{ route('super-admin.academic-years.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate name based on dates
    $('#start_date, #end_date').change(function() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            var startYear = new Date(startDate).getFullYear();
            var endYear = new Date(endDate).getFullYear();
            if (startYear && endYear) {
                $('#name').val(startYear + '-' + endYear);
            }
        }
    });
</script>
@endpush