{{-- resources/views/teacher/homework/show.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Homework Details - ' . $homework->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Homework Details: {{ $homework->title }}
            <div class="float-end">
                <a href="{{ route('teacher.homework.edit', $homework) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('teacher.homework.submissions', $homework) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-users me-1"></i> View Submissions
                </a>
                <a href="{{ route('teacher.homework.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4>{{ $homework->title }}</h4>
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p>{{ $homework->description }}</p>
                    </div>
                    
                    @if($homework->attachments)
                        <div class="mb-3">
                            <strong>Attachments:</strong>
                            <ul>
                                @foreach(json_decode($homework->attachments, true) ?? [] as $index => $att)
                                    <li>
                                        <a href="{{ route('teacher.homework.download-attachment', [$homework->id, $index]) }}" target="_blank">
                                            {{ $att['name'] }}
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
                            <p><strong>Class:</strong> {{ $homework->class->name }}</p>
                            <p><strong>Section:</strong> {{ $homework->section->name }}</p>
                            <p><strong>Subject:</strong> {{ $homework->subject->name }} ({{ $homework->subject->code }})</p>
                            <p><strong>Submission Date:</strong> {{ $homework->submission_date->format('d-m-Y') }}</p>
                            <p><strong>Total Marks:</strong> {{ $homework->total_marks ?? 'Not specified' }}</p>
                            <p><strong>Status:</strong> 
                                @if($homework->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($homework->status == 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @else
                                    <span class="badge bg-danger">Expired</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Students</h6>
                            <h3>{{ $stats['total_students'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Submissions</h6>
                            <h3>{{ $stats['submitted'] }}</h3>
                            <small>{{ $stats['submission_rate'] }}%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Late Submissions</h6>
                            <h3>{{ $stats['late'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h6>Graded</h6>
                            <h3>{{ $stats['graded'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection