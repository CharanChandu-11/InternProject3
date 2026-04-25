{{-- resources/views/student/exams/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Exams')

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Upcoming Exams</h6>
                    <h2 class="mb-0">{{ $stats['upcoming'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h6 class="card-title">Ongoing Exams</h6>
                    <h2 class="mb-0">{{ $stats['ongoing'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Completed Exams</h6>
                    <h2 class="mb-0">{{ $stats['completed'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Exam Schedule
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Exams</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="exam_type_id" class="form-select">
                            <option value="">All Exam Types</option>
                            @foreach($examTypes as $type)
                                <option value="{{ $type->id }}" {{ request('exam_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('student.exams') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Exams List -->
            @forelse($exams as $exam)
                @php
                    $today = Carbon\Carbon::today();
                    $examDate = $exam->exam_date;
                    $isUpcoming = $examDate > $today;
                    $isOngoing = $examDate == $today;
                    $isCompleted = $examDate < $today;
                    $daysRemaining = $today->diffInDays($examDate, false);
                @endphp
                
                <div class="card mb-3 {{ $isOngoing ? 'border-warning' : ($isCompleted ? 'border-secondary' : 'border-primary') }}">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">{{ $exam->exam->name }}</h5>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-book me-1"></i> {{ $exam->subject->name }}
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-chalkboard-user me-1"></i> {{ $exam->subject->code }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i>
                                    <strong>Date:</strong>
                                    {{ $examDate->format('d M, Y') }}
                                </div>
                                <div>
                                    <i class="fas fa-clock text-primary me-1"></i>
                                    <strong>Time:</strong>
                                    {{ Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} - 
                                    {{ Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                @if($isUpcoming)
                                    <span class="badge bg-primary">Upcoming</span>
                                    <div class="small text-muted mt-1">{{ $daysRemaining }} days left</div>
                                @elseif($isOngoing)
                                    <span class="badge bg-warning">Ongoing</span>
                                @else
                                    <a href="{{ route('student.results.show', $exam->exam) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-chart-line me-1"></i> View Results
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-secondary">Room: {{ $exam->room_number ?? 'TBA' }}</span>
                            <span class="badge bg-info">Max Marks: {{ $exam->total_marks + ($exam->practical_marks ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No exams found.
                </div>
            @endforelse
            
            {{ $exams->links() }}
        </div>
    </div>
</div>
@endsection