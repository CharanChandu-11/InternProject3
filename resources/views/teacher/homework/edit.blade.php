{{-- resources/views/teacher/homework/edit.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Edit Homework - ' . $homework->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Homework: {{ $homework->title }}
            <div class="float-end">
                <a href="{{ route('teacher.homework.show', $homework) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye me-1"></i> View
                </a>
                <a href="{{ route('teacher.homework.submissions', $homework) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-users me-1"></i> Submissions
                </a>
                <a href="{{ route('teacher.homework.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-list me-1"></i> All Homework
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.homework.update', $homework) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $homework->title) }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" {{ old('status', $homework->status) == 'active' ? 'selected' : '' }}>Active (Visible to students)</option>
                            <option value="draft" {{ old('status', $homework->status) == 'draft' ? 'selected' : '' }}>Draft (Not visible to students)</option>
                            <option value="expired" {{ old('status', $homework->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Total Marks</label>
                        <input type="number" name="total_marks" class="form-control" value="{{ old('total_marks', $homework->total_marks ?? 100) }}">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-control @error('class_id') is-invalid @enderror" id="classSelect" required>
                            <option value="">Select Class</option>
                            @foreach($classSections as $cs)
                                <option value="{{ $cs['class_id'] }}" {{ old('class_id', $homework->class_id) == $cs['class_id'] ? 'selected' : '' }}>
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
                               value="{{ old('submission_date', $homework->submission_date->format('Y-m-d')) }}" required>
                        @error('submission_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="5" required>{{ old('description', $homework->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <!-- Current Attachments -->
                    @php
                        $attachments = json_decode($homework->attachments, true) ?? [];
                    @endphp
                    
                    @if(count($attachments) > 0)
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Current Attachments</label>
                            <div class="list-group">
                                @foreach($attachments as $index => $attachment)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-paperclip me-2"></i>
                                            <a href="{{ route('teacher.homework.download-attachment', [$homework->id, $index]) }}" target="_blank">
                                                {{ $attachment['name'] }}
                                            </a>
                                            <small class="text-muted ms-2">
                                                ({{ number_format($attachment['size'] / 1024, 2) }} KB)
                                            </small>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="remove_attachments[]" value="{{ $index }}" 
                                                   class="form-check-input" id="remove_att_{{ $index }}">
                                            <label class="form-check-label text-danger" for="remove_att_{{ $index }}">
                                                Remove
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Check the box to remove attachments when updating</small>
                        </div>
                    @endif
                    
                    <!-- New Attachments -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Add New Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                        <small class="text-muted">You can upload multiple files (Max: 10MB each). Existing attachments will be kept unless removed.</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Homework
                    </button>
                    <a href="{{ route('teacher.homework.show', $homework) }}" class="btn btn-secondary">
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
    // Store current values for pre-selection
    var currentClassId = '{{ old("class_id", $homework->class_id) }}';
    var currentSectionId = '{{ old("section_id", $homework->section_id) }}';
    var currentSubjectId = '{{ old("subject_id", $homework->subject_id) }}';
    
    // Load sections when class changes
    function loadSections(classId, selectedSectionId = null) {
        if (classId) {
            $.ajax({
                url: '/teacher/sections/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionSelect');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">Select Section</option>');
                    $.each(data, function(key, section) {
                        var selected = (selectedSectionId == section.id) ? 'selected' : '';
                        sectionSelect.append('<option value="' + section.id + '" ' + selected + '>' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionSelect').empty().append('<option value="">Select Section</option>');
        }
    }
    
    // Load subjects when class changes
    function loadSubjects(classId, selectedSubjectId = null) {
        if (classId) {
            $.ajax({
                url: '/teacher/subjects/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var subjectSelect = $('#subjectSelect');
                    subjectSelect.empty();
                    subjectSelect.append('<option value="">Select Subject</option>');
                    $.each(data, function(key, subject) {
                        var selected = (selectedSubjectId == subject.id) ? 'selected' : '';
                        subjectSelect.append('<option value="' + subject.id + '" ' + selected + '>' + subject.name + ' (' + subject.code + ')</option>');
                    });
                }
            });
        } else {
            $('#subjectSelect').empty().append('<option value="">Select Subject</option>');
        }
    }
    
    // Initialize on page load
    $(document).ready(function() {
        if (currentClassId) {
            loadSections(currentClassId, currentSectionId);
            loadSubjects(currentClassId, currentSubjectId);
        }
    });
    
    // Handle class change
    $('#classSelect').change(function() {
        var classId = $(this).val();
        if (classId) {
            loadSections(classId);
            loadSubjects(classId);
        } else {
            $('#sectionSelect').empty().append('<option value="">Select Section</option>');
            $('#subjectSelect').empty().append('<option value="">Select Subject</option>');
        }
    });
</script>
@endpush