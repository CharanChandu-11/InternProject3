{{-- resources/views/parent/children/homework.blade.php --}}
@extends('layouts.parent')

@section('title', 'Homework - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Homework: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($homeworks->count() > 0)
                @foreach($homeworks as $homework)
                    @php
                        $submission = $submissions[$homework->id] ?? null;
                        $isOverdue = $homework->submission_date < now() && !$submission;
                    @endphp
                    <div class="card mb-3 {{ $isOverdue ? 'border-danger' : ($submission ? 'border-success' : 'border-warning') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title">{{ $homework->title }}</h5>
                                <span class="badge bg-{{ $isOverdue ? 'danger' : ($submission ? 'success' : 'warning') }}">
                                    {{ $isOverdue ? 'Overdue' : ($submission ? 'Submitted' : 'Pending') }}
                                </span>
                            </div>
                            <p class="text-muted small">
                                <i class="fas fa-book me-1"></i> {{ $homework->subject->name }}
                                | <i class="fas fa-user me-1"></i> {{ $homework->teacher->name }}
                            </p>
                            <p>{{ Str::limit($homework->description, 150) }}</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i> Due: {{ $homework->submission_date->format('d-m-Y') }}
                                        @if(!$submission && !$isOverdue)
                                            <span class="text-warning">({{ now()->diffInDays($homework->submission_date, false) }} days left)</span>
                                        @endif
                                    </small>
                                </div>
                                @if($submission && $submission->status == 'graded')
                                    <div class="col-md-6 text-end">
                                        <small class="text-success">
                                            <i class="fas fa-star me-1"></i> Marks: {{ $submission->obtained_marks }}/{{ $homework->total_marks ?? 'N/A' }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('parent.children.homework.detail', [$student, $homework]) }}" class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No homework assigned.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection