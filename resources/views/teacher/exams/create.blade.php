{{-- resources/views/teacher/exams/create.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Schedule New Exam')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i> Schedule New Exam
            <div class="float-end">
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Exams
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.exams.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                        <select name="exam_type_id" class="form-control @error('exam_type_id') is-invalid @enderror" required>
                            <option value="">Select Exam Type</option>
                            @foreach($examTypes as $type)
                                <option value="{{ $type->id }}" {{ old('exam_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('exam_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                        <input type="text" name="exam_name" class="form-control @error('exam_name') is-invalid @enderror" 
                               value="{{ old('exam_name') }}" required>
                        <small class="text-muted">e.g., Mid Term Examination, Final Examination</small>
                        @error('exam_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Exam Date <span class="text-danger">*</span></label>
                        <input type="date" name="exam_date" class="form-control @error('exam_date') is-invalid @enderror" 
                               value="{{ old('exam_date', now()->addDays(7)->format('Y-m-d')) }}" required>
                        @error('exam_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                               value="{{ old('start_time', '09:00') }}" required>
                        @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                               value="{{ old('end_time', '12:00') }}" required>
                        @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="form-control" value="{{ old('room_number') }}">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Marks <span class="text-danger">*</span></label>
                        <input type="number" name="total_marks" class="form-control @error('total_marks') is-invalid @enderror" 
                               value="{{ old('total_marks', 100) }}" required>
                        @error('total_marks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Passing Marks <span class="text-danger">*</span></label>
                        <input type="number" name="passing_marks" class="form-control @error('passing_marks') is-invalid @enderror" 
                               value="{{ old('passing_marks', 40) }}" required>
                        @error('passing_marks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Practical Marks</label>
                        <input type="number" name="practical_marks" class="form-control" value="{{ old('practical_marks', 0) }}" min="0">
                        <small class="text-muted">Leave 0 if no practical exam</small>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>
                
                <div class="alert alert-info mt-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Students will be able to see this exam in their dashboard once created.
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Schedule Exam
                    </button>
                    <a href="{{ route('teacher.exams.index') }}" class="btn btn-secondary">
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
                url: '/teacher/exams/sections/by-class/' + classId,
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
                url: '/teacher/exams/subjects/by-class/' + classId,
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