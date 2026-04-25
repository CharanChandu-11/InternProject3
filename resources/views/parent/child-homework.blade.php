{{-- resources/views/parent/child-homework.blade.php --}}
@extends('layouts.parent')

@section('title', $student->user->name . ' - Homework')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-book me-2"></i> 
                            Homework - {{ $student->user->name }}
                        </div>
                        <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                                <i class="fas fa-clock me-1"></i> Pending
                                @if($pendingHomework->count() > 0)
                                    <span class="badge bg-danger ms-1">{{ $pendingHomework->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#submitted" type="button">
                                <i class="fas fa-check-circle me-1"></i> Submitted
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#graded" type="button">
                                <i class="fas fa-star me-1"></i> Graded
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pending">
                            @forelse($pendingHomework as $homework)
                                <div class="homework-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <h6 class="mb-0">{{ $homework->title }}</h6>
                                                <span class="badge bg-primary">{{ $homework->subject->name }}</span>
                                            </div>
                                            <p class="text-muted mb-2">{{ Str::limit($homework->description, 120) }}</p>
                                            <small class="text-warning">
                                                <i class="fas fa-calendar-alt me-1"></i> 
                                                Due: {{ $homework->submission_date->format('d M, Y') }}
                                                @if($homework->submission_date->isPast())
                                                    <span class="badge bg-danger ms-2">Overdue</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                    <h5>No Pending Homework!</h5>
                                    <p class="text-muted">All homework completed.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <div class="tab-pane fade" id="submitted">
                            @forelse($submittedHomework as $submission)
                                <div class="homework-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <h6 class="mb-0">{{ $submission->homework->title }}</h6>
                                                <span class="badge bg-info">{{ $submission->homework->subject->name }}</span>
                                                @if($submission->is_late)
                                                    <span class="badge bg-warning">Late Submission</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i> 
                                                Submitted: {{ $submission->submitted_at->format('d M, Y h:i A') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">No submitted homework yet.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <div class="tab-pane fade" id="graded">
                            @forelse($gradedHomework as $submission)
                                <div class="homework-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <h6 class="mb-0">{{ $submission->homework->title }}</h6>
                                                <span class="badge bg-success">{{ $submission->homework->subject->name }}</span>
                                                <span class="badge bg-primary">
                                                    Marks: {{ $submission->obtained_marks }}/{{ $submission->homework->total_marks ?? 100 }}
                                                </span>
                                            </div>
                                            @if($submission->feedback)
                                                <small class="text-muted">
                                                    <i class="fas fa-comment me-1"></i> {{ $submission->feedback }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-star fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">No graded homework yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .homework-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid var(--primary);
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .homework-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush