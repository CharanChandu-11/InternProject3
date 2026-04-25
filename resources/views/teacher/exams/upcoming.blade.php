{{-- resources/views/teacher/exams/upcoming.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Upcoming Exams')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Upcoming Exams
            <div class="float-end">
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-list me-1"></i> All Exams
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($upcomingExams->count() > 0)
                <div class="row">
                    @foreach($upcomingExams as $exam)
                        @php
                            $daysLeft = \Carbon\Carbon::today()->diffInDays($exam->exam_date, false);
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title">{{ $exam->exam->name }}</h5>
                                        <span class="badge bg-primary">{{ $exam->exam->examType->name ?? '' }}</span>
                                    </div>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        {{ $exam->subject->name }} ({{ $exam->subject->code }})
                                    </h6>
                                    <hr>
                                    <div class="mb-2">
                                        <i class="fas fa-building me-2"></i> 
                                        <strong>Class:</strong> {{ $exam->class->name }} - Section {{ $exam->section->name }}
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-day me-2"></i> 
                                        <strong>Date:</strong> {{ $exam->exam_date->format('l, F j, Y') }}
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-clock me-2"></i> 
                                        <strong>Time:</strong> {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} - 
                                        {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-door-open me-2"></i> 
                                        <strong>Room:</strong> {{ $exam->room_number ?? 'Not assigned' }}
                                    </div>
                                    <div class="mt-3">
                                        @if($daysLeft == 0)
                                            <span class="badge bg-warning">Today</span>
                                        @elseif($daysLeft == 1)
                                            <span class="badge bg-info">Tomorrow</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $daysLeft }} days left</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No upcoming exams scheduled.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #4361ee;
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
</style>
@endpush