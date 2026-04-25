{{-- resources/views/super-admin/classes/create.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Add New Class')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i> Add New Class
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.classes.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               placeholder="e.g., Class 1, Class 2, etc." value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Numeric Name (Optional)</label>
                        <input type="number" name="numeric_name" class="form-control @error('numeric_name') is-invalid @enderror" 
                               placeholder="e.g., 1, 2, 3..." value="{{ old('numeric_name') }}">
                        <small class="text-muted">Used for sorting and promotion logic</small>
                        @error('numeric_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $year->is_current ? $year->id : '') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) (Current) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class Teacher</label>
                        <select name="class_teacher_id" class="form-control @error('class_teacher_id') is-invalid @enderror">
                            <option value="">Select Class Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('class_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->employee->designation ?? 'Teacher' }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Section Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                               value="{{ old('capacity', 40) }}" required>
                        <small class="text-muted">Maximum students per section</small>
                        @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <h5 class="mt-4 mb-3">Sections</h5>
                <div id="sections-container">
                    <div class="section-row row mb-3">
                        <div class="col-md-5">
                            <input type="text" name="sections[0][name]" class="form-control" placeholder="Section Name (e.g., A, B, C)" required>
                        </div>
                        <div class="col-md-5">
                            <input type="number" name="sections[0][capacity]" class="form-control" placeholder="Section Capacity" value="40" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-section">Remove</button>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-section" class="btn btn-sm btn-secondary mt-2">
                    <i class="fas fa-plus"></i> Add Section
                </button>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Class
                    </button>
                    <a href="{{ route('super-admin.classes.index') }}" class="btn btn-secondary">
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
    let sectionIndex = 1;
    
    $('#add-section').click(function() {
        const newRow = `
            <div class="section-row row mb-3">
                <div class="col-md-5">
                    <input type="text" name="sections[${sectionIndex}][name]" class="form-control" placeholder="Section Name (e.g., A, B, C)" required>
                </div>
                <div class="col-md-5">
                    <input type="number" name="sections[${sectionIndex}][capacity]" class="form-control" placeholder="Section Capacity" value="40" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-section">Remove</button>
                </div>
            </div>
        `;
        $('#sections-container').append(newRow);
        sectionIndex++;
    });
    
    $(document).on('click', '.remove-section', function() {
        if ($('.section-row').length > 1) {
            $(this).closest('.section-row').remove();
        } else {
            alert('At least one section is required.');
        }
    });
</script>
@endpush