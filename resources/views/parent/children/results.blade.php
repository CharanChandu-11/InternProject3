{{-- resources/views/parent/children/results.blade.php --}}
@extends('layouts.parent')

@section('title', 'Results - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line me-2"></i> Exam Results: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($results->count() > 0)
                @foreach($results as $examId => $examResults)
                    @php
                        $exam = $examResults->first()->examSchedule->exam;
                        $totalMarks = $examResults->sum('total_marks_obtained');
                        $maxMarks = $examResults->sum(function($r) {
                            return $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0);
                        });
                        $percentage = $maxMarks > 0 ? round(($totalMarks / $maxMarks) * 100, 2) : 0;
                    @endphp
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $exam->name }}</strong>
                                <span class="badge bg-{{ $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}">
                                    {{ $percentage }}%
                                </span>
                            </div>
                            <small class="text-muted">{{ $exam->start_date->format('F j, Y') }}</small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Theory</th>
                                            <th>Practical</th>
                                            <th>Total</th>
                                            <th>Max</th>
                                            <th>Percentage</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($examResults as $result)
                                            <tr>
                                                <td>{{ $result->examSchedule->subject->name }}</td>
                                                <td>{{ $result->theory_marks_obtained ?? '-' }}</td>
                                                <td>{{ $result->practical_marks_obtained ?? '-' }}</td>
                                                <td><strong>{{ $result->total_marks_obtained }}</strong></td>
                                                <td>{{ $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0) }}</td>
                                                <td>{{ $result->percentage }}%</td>
                                                <td>
                                                    <span class="badge bg-{{ $result->grade == 'F' ? 'danger' : 'success' }}">
                                                        {{ $result->grade }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </div>
                            </div>
                            <div class="mt-2 text-end">
                                <a href="{{ route('parent.children.results.detail', [$student, $examId]) }}" class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No results published yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection