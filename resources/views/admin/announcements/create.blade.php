{{-- resources/views/admin/announcements/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Announcement')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus-circle me-2"></i> Create New Announcement
        </div>
        <div class="card-body">
            <form action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label>Audience <span class="text-danger">*</span></label>
                            <select name="audience" id="audience" class="form-control @error('audience') is-invalid @enderror" required>
                                <option value="all" {{ old('audience') == 'all' ? 'selected' : '' }}>Everyone</option>
                                <option value="students" {{ old('audience') == 'students' ? 'selected' : '' }}>Students Only</option>
                                <option value="parents" {{ old('audience') == 'parents' ? 'selected' : '' }}>Parents Only</option>
                                <option value="teachers" {{ old('audience') == 'teachers' ? 'selected' : '' }}>Teachers Only</option>
                                <option value="employees" {{ old('audience') == 'employees' ? 'selected' : '' }}>Employees Only</option>
                                <option value="specific_classes" {{ old('audience') == 'specific_classes' ? 'selected' : '' }}>Specific Classes</option>
                            </select>
                            @error('audience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div id="specific-classes-section" class="mb-3" style="display: none;">
                    <label>Select Classes <span class="text-danger">*</span></label>
                    <div class="row">
                        @foreach($classes as $class)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="specific_classes[]" value="{{ $class->id }}" class="form-check-input" id="class_{{ $class->id }}">
                                    <label class="form-check-label" for="class_{{ $class->id }}">
                                        {{ $class->name }} ({{ $class->sections->pluck('name')->implode(', ') }})
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Publish Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="publish_date" class="form-control @error('publish_date') is-invalid @enderror" value="{{ old('publish_date', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('publish_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Expiry Date</label>
                            <input type="datetime-local" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}">
                            <small class="text-muted">Leave blank for no expiry</small>
                            @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Content <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="8" required>{{ old('content') }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" {{ old('is_published') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published">Publish immediately</label>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('audience').addEventListener('change', function() {
        var section = document.getElementById('specific-classes-section');
        section.style.display = this.value === 'specific_classes' ? 'block' : 'none';
    });
    // Trigger on load
    document.getElementById('audience').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection