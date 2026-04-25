{{-- resources/views/student/homework-show.blade.php --}}
@extends('layouts.student')

@section('title', 'Homework Details')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-book me-2"></i> {{ $homework->title }}
                <a href="{{ route('student.homework') }}" class="btn btn-sm btn-secondary float-end">Back</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Subject:</strong> {{ $homework->subject->name }}</p>
                        <p><strong>Teacher:</strong> {{ $homework->teacher->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Due Date:</strong> {{ $homework->submission_date->format('d M, Y') }}</p>
                        <p><strong>Status:</strong>
                            @if($submission)
                                @if($submission->status == 'graded')
                                    <span class="badge bg-success">Graded</span>
                                @elseif($submission->is_late)
                                    <span class="badge bg-warning">Late Submitted</span>
                                @else
                                    <span class="badge bg-info">Submitted</span>
                                @endif
                            @elseif($homework->submission_date->isPast())
                                <span class="badge bg-danger">Overdue</span>
                            @else
                                <span class="badge bg-primary">Pending</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6>Description:</h6>
                    <p>{{ $homework->description }}</p>
                </div>
                
                @if($homework->attachments)
                    <div class="mb-4">
                        <h6>Attachments:</h6>
                        @foreach($homework->attachments as $attachment)
                            <a href="{{ Storage::url($attachment['path']) }}" class="btn btn-sm btn-outline-primary me-2" target="_blank">
                                <i class="fas fa-download me-1"></i> {{ $attachment['name'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
                
                @if($submission && $submission->status == 'graded')
                    <div class="alert alert-success">
                        <h5>Your Grade</h5>
                        <p><strong>Marks Obtained:</strong> {{ $submission->obtained_marks }}/{{ $homework->total_marks ?? 100 }}</p>
                        @if($submission->feedback)
                            <p><strong>Feedback:</strong> {{ $submission->feedback }}</p>
                        @endif
                    </div>
                @endif
                
                @if(!$submission && !$homework->submission_date->isPast())
                    <div class="card bg-light">
                        <div class="card-header">
                            <i class="fas fa-upload me-2"></i> Submit Homework
                        </div>
                        <div class="card-body">
                            <form action="{{ route('student.homework.submit', $homework) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label>Your Answer / Submission</label>
                                    <textarea name="submission_text" class="form-control" rows="5" placeholder="Write your answer here..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Attach Files (Optional)</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple>
                                    <small class="text-muted">Max size: 10MB per file. Allowed: pdf, doc, docx, jpg, png</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Homework</button>
                            </form>
                        </div>
                    </div>
                @elseif($submission)
                    <div class="alert alert-info">
                        <h5>Your Submission</h5>
                        <p>Submitted on: {{ $submission->submitted_at->format('d M, Y h:i A') }}</p>
                        @if($submission->submission_text)
                            <div class="mt-2">
                                <strong>Your Answer:</strong>
                                <p>{{ $submission->submission_text }}</p>
                            </div>
                        @endif
                        @if($submission->attachments)
                            <div class="mt-2">
                                <strong>Attached Files:</strong>
                                @foreach($submission->attachments as $attachment)
                                    <a href="{{ Storage::url($attachment['path']) }}" class="btn btn-sm btn-outline-primary me-2" target="_blank">
                                        <i class="fas fa-download me-1"></i> {{ $attachment['name'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif($homework->submission_date->isPast())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> This homework is overdue. Submission is no longer accepted.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection