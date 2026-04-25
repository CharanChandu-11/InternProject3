{{-- resources/views/student/homework/index.blade.php --}}
@extends('layouts.student')

@section('title', 'My Homework')

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Homework</h6>
                    <h2 class="mb-0">{{ $stats['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="card-title">Pending</h6>
                    <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Overdue</h6>
                    <h2 class="mb-0">{{ $stats['overdue'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Graded</h6>
                    <h2 class="mb-0">{{ $stats['graded'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Homework Assignments
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('student.homework') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Homework List -->
            @forelse($homeworks as $homework)
                @php
                    $submission = $submissions[$homework->id] ?? null;
                    $isOverdue = $homework->submission_date < now() && !$submission;
                    $isSubmitted = !is_null($submission);
                    $isGraded = $submission && $submission->status == 'graded';
                    $daysRemaining = now()->diffInDays($homework->submission_date, false);
                @endphp
                
                <div class="card mb-3 {{ $isOverdue ? 'border-danger' : ($isSubmitted ? 'border-success' : 'border-warning') }}">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">{{ $homework->title }}</h5>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-book me-1"></i> {{ $homework->subject->name }}
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-chalkboard-user me-1"></i> {{ $homework->teacher->name }}
                                        </p>
                                        <p class="mb-2">{{ Str::limit($homework->description, 100) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i>
                                    <strong>Due Date:</strong>
                                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                        {{ $homework->submission_date->format('d M, Y') }}
                                    </span>
                                </div>
                                @if(!$isSubmitted && !$isOverdue)
                                    <div class="text-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $daysRemaining }} days left
                                    </div>
                                @elseif($isOverdue)
                                    <div class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Overdue
                                    </div>
                                @elseif($isGraded)
                                    <div class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Graded: {{ $submission->obtained_marks }}/{{ $homework->total_marks ?? 'N/A' }}
                                    </div>
                                @elseif($isSubmitted)
                                    <div class="text-info">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        Submitted
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="{{ route('student.homework.show', $homework) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </a>
                            </div>
                        </div>
                        <div class="mt-2">
                            @if($isSubmitted && !$isGraded)
                                <span class="badge bg-info">Awaiting Grading</span>
                            @endif
                            @if($homework->total_marks)
                                <span class="badge bg-secondary">Max Marks: {{ $homework->total_marks }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No homework assignments found.
                </div>
            @endforelse
            
            {{ $homeworks->links() }}
        </div>
    </div>
</div>
@endsection