{{-- resources/views/student/exams.blade.php --}}
@extends('layouts.student')

@section('title', 'Upcoming Exams')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt"></i> Upcoming Examinations
                </div>
                <div class="card-body">
                    @if($exams->count() > 0)
                        <div class="row">
                            @foreach($exams as $exam)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span class="badge bg-primary mb-2">{{ $exam->exam->name }}</span>
                                                    <h5 class="mb-1">{{ $exam->subject->name }}</h5>
                                                </div>
                                                <div class="text-end">
                                                    <div class="small text-muted">Total Marks</div>
                                                    <div class="fw-bold">{{ $exam->total_marks }}</div>
                                                </div>
                                            </div>
                                            
                                            <div class="row g-3 mt-2">
                                                <div class="col-6">
                                                    <div class="bg-light rounded-3 p-2 text-center">
                                                        <i class="fas fa-calendar-alt text-primary mb-1"></i>
                                                        <div class="small text-muted">Date</div>
                                                        <div class="fw-bold">{{ $exam->exam_date->format('d M, Y') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-3 p-2 text-center">
                                                        <i class="fas fa-clock text-primary mb-1"></i>
                                                        <div class="small text-muted">Time</div>
                                                        <div class="fw-bold small">{{ $exam->start_time->format('h:i A') }} - {{ $exam->end_time->format('h:i A') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="fas fa-door-open text-muted me-1"></i>
                                                        <small>Room: {{ $exam->room_number ?? 'TBA' }}</small>
                                                    </div>
                                                    @php
                                                        $daysLeft = now()->diffInDays($exam->exam_date, false);
                                                    @endphp
                                                    @if($daysLeft <= 3 && $daysLeft > 0)
                                                        <span class="badge bg-warning">{{ ceil($daysLeft) }} days left</span>
                                                    @elseif($daysLeft > 0)
                                                        <span class="badge bg-info">{{ ceil($daysLeft) }} days left</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-alt fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No upcoming exams scheduled.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection