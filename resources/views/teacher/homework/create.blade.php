{{-- resources/views/teacher/homework/create.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Assign Homework')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i> Assign New Homework
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.homework.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active (Visible to students)</option>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft (Not visible to students)</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Total Marks</label>
                        <input type="number" name="total_marks" class="form-control" value="{{ old('total_marks', 100) }}">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-control @error('class_id') is-invalid @enderror" id="classSelect" required>
                            <option value="">Select Class</option>
                            @foreach($classSections as $cs)
                                <option value="{{ $cs['class_id'] }}" {{ old('class_id') == $cs['class_id'] ? 'selected' : '' }}>
                                    {{ $cs['class_name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <select name="section_id" class="form-control @error('section_id') is-invalid @enderror" id="sectionSelect" required>
                            <option value="">Select Section</option>
                        </select>
                        @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" id="subjectSelect" required>
                            <option value="">Select Subject</option>
                        </select>
                        @error('subject_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Submission Date <span class="text-danger">*</span></label>
                        <input type="date" name="submission_date" class="form-control @error('submission_date') is-invalid @enderror" 
                               value="{{ old('submission_date', now()->addDays(7)->format('Y-m-d')) }}" required>
                        @error('submission_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="5" required>{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                        <small class="text-muted">You can upload multiple files (Max: 10MB each)</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Assign Homework
                    </button>
                    <a href="{{ route('teacher.homework.index') }}" class="btn btn-secondary">
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
    // Load sections when class changes
    $('#classSelect').change(function() {
        var classId = $(this).val();
        if (classId) {
            // Load sections
            $.ajax({
                url: '/teacher/sections/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionSelect');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">Select Section</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
            
            // Load subjects for this class
            $.ajax({
                url: '/teacher/subjects/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var subjectSelect = $('#subjectSelect');
                    subjectSelect.empty();
                    subjectSelect.append('<option value="">Select Subject</option>');
                    $.each(data, function(key, subject) {
                        subjectSelect.append('<option value="' + subject.id + '">' + subject.name + ' (' + subject.code + ')</option>');
                    });
                }
            });
        } else {
            $('#sectionSelect').empty().append('<option value="">Select Section</option>');
            $('#subjectSelect').empty().append('<option value="">Select Subject</option>');
        }
    });
</script>
@endpush