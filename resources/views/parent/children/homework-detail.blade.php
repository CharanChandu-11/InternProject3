{{-- resources/views/parent/children/homework-detail.blade.php --}}
@extends('layouts.parent')

@section('title', 'Homework Detail - ' . $homework->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Homework: {{ $homework->title }}
            <div class="float-end">
                <a href="{{ route('parent.children.homework', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4>{{ $homework->title }}</h4>
                    <p>{{ $homework->description }}</p>
                    
                    @if($homework->attachments)
                        <div class="mt-3">
                            <strong>Attachments:</strong>
                            <ul>
                                @foreach(json_decode($homework->attachments, true) ?? [] as $attachment)
                                    <li>
                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank">
                                            {{ $attachment['name'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Information</h6>
                            <hr>
                            <p><strong>Subject:</strong> {{ $homework->subject->name }}</p>
                            <p><strong>Teacher:</strong> {{ $homework->teacher->name }}</p>
                            <p><strong>Due Date:</strong> {{ $homework->submission_date->format('d-m-Y') }}</p>
                            @if($homework->total_marks)
                                <p><strong>Total Marks:</strong> {{ $homework->total_marks }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            @if($submission)
                <div class="mt-4">
                    <h5>Student Submission</h5>
                    <div class="alert alert-info">
                        <p><strong>Submitted On:</strong> {{ $submission->submitted_at->format('d-m-Y h:i A') }}</p>
                        @if($submission->submission_text)
                            <p><strong>Answer:</strong> {{ $submission->submission_text }}</p>
                        @endif
                        @if($submission->attachments)
                            <p><strong>Attachments:</strong></p>
                            <ul>
                                @foreach(json_decode($submission->attachments, true) ?? [] as $attachment)
                                    <li>
                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank">
                                            {{ $attachment['name'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if($submission->status == 'graded')
                            <p><strong>Marks:</strong> {{ $submission->obtained_marks }}/{{ $homework->total_marks ?? 'N/A' }}</p>
                            <p><strong>Feedback:</strong> {{ $submission->feedback ?? 'No feedback' }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i> Student has not submitted this homework yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection