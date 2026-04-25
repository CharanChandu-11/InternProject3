{{-- resources/views/teacher/exams/edit.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Edit Exam - ' . $examSchedule->exam->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Exam: {{ $examSchedule->exam->name }}
            <div class="float-end">
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Exams
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.exams.update', $examSchedule) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exam Name</label>
                        <input type="text" class="form-control" value="{{ $examSchedule->exam->name }}" disabled>
                        <small class="text-muted">Exam name cannot be changed</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exam Type</label>
                        <input type="text" class="form-control" value="{{ $examSchedule->exam->examType->name ?? 'N/A' }}" disabled>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Class</label>
                        <input type="text" class="form-control" value="{{ $examSchedule->class->name }}" disabled>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Section</label>
                        <input type="text" class="form-control" value="{{ $examSchedule->section->name }}" disabled>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" value="{{ $examSchedule->subject->name }} ({{ $examSchedule->subject->code }})" disabled>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Exam Date <span class="text-danger">*</span></label>
                        <input type="date" name="exam_date" class="form-control @error('exam_date') is-invalid @enderror" 
                               value="{{ old('exam_date', $examSchedule->exam_date->format('Y-m-d')) }}" required>
                        @error('exam_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                               value="{{ old('start_time', \Carbon\Carbon::parse($examSchedule->start_time)->format('H:i')) }}" required>
                        @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                               value="{{ old('end_time', \Carbon\Carbon::parse($examSchedule->end_time)->format('H:i')) }}" required>
                        @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $examSchedule->room_number) }}">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Marks <span class="text-danger">*</span></label>
                        <input type="number" name="total_marks" class="form-control @error('total_marks') is-invalid @enderror" 
                               value="{{ old('total_marks', $examSchedule->total_marks) }}" required>
                        @error('total_marks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Passing Marks <span class="text-danger">*</span></label>
                        <input type="number" name="passing_marks" class="form-control @error('passing_marks') is-invalid @enderror" 
                               value="{{ old('passing_marks', $examSchedule->passing_marks) }}" required>
                        @error('passing_marks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Practical Marks</label>
                        <input type="number" name="practical_marks" class="form-control" 
                               value="{{ old('practical_marks', $examSchedule->practical_marks ?? 0) }}" min="0">
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $examSchedule->exam->description) }}</textarea>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Changes to marks and schedule will affect student view.
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Exam
                    </button>
                    <a href="{{ route('teacher.exams.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="button" class="btn btn-danger float-end" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-1"></i> Delete Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teacher.exams.destroy', $examSchedule) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this exam?</p>
                    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection