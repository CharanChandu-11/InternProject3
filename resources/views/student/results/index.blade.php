{{-- resources/views/student/results/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Exam Results')

@section('content')
<div class="animate-fadeInUp">
    <!-- Overall Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Exams</h6>
                    <h2 class="mb-0">{{ $overallStats['total_exams'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Average Percentage</h6>
                    <h2 class="mb-0">{{ round($overallStats['average_percentage'], 2) }}%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Subjects Passed</h6>
                    <h2 class="mb-0">{{ $overallStats['total_passed'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Subjects Failed</h6>
                    <h2 class="mb-0">{{ $overallStats['total_failed'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line me-2"></i> Exam Results
            <div class="float-end">
                <a href="{{ route('student.results.summary') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-chart-pie me-1"></i> Performance Summary
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
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
                    <div class="col-md-3">
                        <select name="year" class="form-select">
                            <option value="">All Years</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('student.results') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Results by Exam -->
            @forelse($groupedResults as $examId => $examResults)
                @php
                    $exam = $examResults->first()->examSchedule->exam;
                    $totalObtained = $examResults->sum('total_marks_obtained');
                    $totalMax = $examResults->sum(function($r) {
                        return $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0);
                    });
                    $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
                @endphp
                
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $exam->name }}</h6>
                            <span class="badge bg-{{ $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}">
                                {{ $percentage }}%
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th class="text-center">Theory</th>
                                        <th class="text-center">Practical</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Max Marks</th>
                                        <th class="text-center">Percentage</th>
                                        <th class="text-center">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examResults as $result)
                                    <tr>
                                        <td>{{ $result->examSchedule->subject->name }} ({{ $result->examSchedule->subject->code }})</td>
                                        <td class="text-center">{{ $result->theory_marks_obtained ?? '-' }}</td>
                                        <td class="text-center">{{ $result->practical_marks_obtained ?? '-' }}</td>
                                        <td class="text-center fw-bold">{{ $result->total_marks_obtained }}</td>
                                        <td class="text-center">{{ $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0) }}</td>
                                        <td class="text-center">{{ $result->percentage }}%</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $result->grade == 'F' ? 'danger' : 'success' }}">
                                                {{ $result->grade }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th colspan="3" class="text-center">{{ $totalObtained }}</th>
                                        <th class="text-center">{{ $totalMax }}</th>
                                        <th class="text-center">{{ $percentage }}%</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <a href="{{ route('student.results.show', $exam) }}" class="btn btn-sm btn-primary">
                                View Details <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No results found.
                </div>
            @endforelse
            
            {{ $results->links() }}
        </div>
    </div>
</div>
@endsection