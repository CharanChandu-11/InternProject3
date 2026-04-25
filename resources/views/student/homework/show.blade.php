{{-- resources/views/student/homework/show.blade.php --}}
@extends('layouts.student')

@section('title', $homework->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Homework Details
            <div class="float-end">
                <a href="{{ route('student.homework') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>{{ $homework->title }}</h3>
                    <div class="mb-3">
                        <span class="badge bg-primary">{{ $homework->subject->name }}</span>
                        <span class="badge bg-secondary">Teacher: {{ $homework->teacher->name }}</span>
                        @if($homework->total_marks)
                            <span class="badge bg-info">Total Marks: {{ $homework->total_marks }}</span>
                        @endif
                    </div>
                    
                    <div class="mb-4">
                        <h6>Description:</h6>
                        <p>{{ $homework->description }}</p>
                    </div>
                    
                    @if(count($attachments) > 0)
                        <div class="mb-4">
                            <h6>Attachments:</h6>
                            <ul class="list-group">
                                @foreach($attachments as $index => $attachment)
                                    <li class="list-group-item">
                                        <i class="fas fa-paperclip me-2"></i>
                                        <a href="{{ route('student.homework.download-attachment', [$homework->id, $index]) }}" target="_blank">
                                            {{ $attachment['name'] }}
                                        </a>
                                        <small class="text-muted ms-2">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Submission Information</h6>
                            <hr>
                            <p class="mb-2">
                                <strong><i class="fas fa-calendar-alt me-2"></i> Due Date:</strong><br>
                                {{ $homework->submission_date->format('l, F j, Y') }}
                                @if($daysRemaining > 0 && !$submission)
                                    <span class="badge bg-warning ms-2">{{ $daysRemaining }} days left</span>
                                @elseif($isOverdue)
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-clock me-2"></i> Due Time:</strong><br>
                                {{ $homework->submission_time ? \Carbon\Carbon::parse($homework->submission_time)->format('h:i A') : 'End of Day' }}
                            </p>
                            
                            @if($submission)
                                <hr>
                                <p class="mb-2">
                                    <strong><i class="fas fa-paper-plane me-2"></i> Submission Status:</strong><br>
                                    @if($submission->status == 'submitted')
                                        <span class="badge bg-info">Submitted (Pending Grading)</span>
                                    @elseif($submission->status == 'late')
                                        <span class="badge bg-warning">Submitted (Late)</span>
                                    @elseif($submission->status == 'graded')
                                        <span class="badge bg-success">Graded</span>
                                    @endif
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-calendar-check me-2"></i> Submitted On:</strong><br>
                                    {{ $submission->submitted_at->format('l, F j, Y h:i A') }}
                                </p>
                                @if($submission->status == 'graded')
                                    <p class="mb-2">
                                        <strong><i class="fas fa-star me-2"></i> Marks Obtained:</strong><br>
                                        <span class="fw-bold">{{ $submission->obtained_marks }}/{{ $homework->total_marks ?? 'N/A' }}</span>
                                        @php
                                            $percentage = $homework->total_marks > 0 ? round(($submission->obtained_marks / $homework->total_marks) * 100, 2) : 0;
                                        @endphp
                                        <span class="badge bg-{{ $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }} ms-2">
                                            {{ $percentage }}%
                                        </span>
                                    </p>
                                    @if($submission->feedback)
                                        <p class="mb-0">
                                            <strong><i class="fas fa-comment me-2"></i> Feedback:</strong><br>
                                            {{ $submission->feedback }}
                                        </p>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submission Form -->
            @if($canSubmit)
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-upload me-2"></i> Submit Homework
                        </div>
                        <div class="card-body">
                            <form action="{{ route('student.homework.submit', $homework) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Your Answer / Submission Text</label>
                                    <textarea name="submission_text" class="form-control" rows="5" placeholder="Write your answer here..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Upload Files (Optional)</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                                    <small class="text-muted">You can upload multiple files (Max: 10MB each)</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Homework
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($submission && $submission->status == 'graded')
                <div class="mt-4 alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    You have already submitted and received grades for this homework.
                </div>
            @elseif($submission && $submission->status != 'graded')
                <div class="mt-4 alert alert-info">
                    <i class="fas fa-hourglass-half me-2"></i>
                    You have already submitted this homework. Waiting for teacher's grading.
                </div>
            @elseif($isOverdue)
                <div class="mt-4 alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This homework is overdue. You cannot submit it now.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection