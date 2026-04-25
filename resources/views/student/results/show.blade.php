{{-- resources/views/student/results/show.blade.php --}}
@extends('layouts.student')

@section('title', 'Result - ' . $exam->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line me-2"></i> Exam Result: {{ $exam->name }}
            <div class="float-end">
                <a href="{{ route('student.results') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Results
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Exam Information -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Exam Date</h6>
                        <p>{{ $exam->start_date->format('F j, Y') }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-tag me-2 text-primary"></i> Exam Type</h6>
                        <p>{{ $exam->examType->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-trophy me-2 text-primary"></i> Your Rank</h6>
                        <p>{{ $rank ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-chart-line me-2 text-primary"></i> Class Average</h6>
                        <p>{{ $classAverage }}%</p>
                    </div>
                </div>
            </div>
            
            <!-- Overall Score Card -->
            <div class="card bg-light mb-4">
                <div class="card-body text-center">
                    <h4>Overall Score</h4>
                    <div class="display-1 fw-bold text-{{ $overallPercentage >= 75 ? 'success' : ($overallPercentage >= 50 ? 'warning' : 'danger') }}">
                        {{ $overallPercentage }}%
                    </div>
                    <p class="mb-0">
                        {{ $totalObtained }} / {{ $totalMaxMarks }} marks obtained
                    </p>
                </div>
            </div>
            
            <!-- Subject-wise Performance -->
            <h5 class="mb-3">Subject-wise Performance</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Code</th>
                            <th class="text-center">Theory</th>
                            <th class="text-center">Practical</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Max Marks</th>
                            <th class="text-center">Percentage</th>
                            <th class="text-center">Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjectPerformance as $subject)
                        <tr>
                            <td><strong>{{ $subject['subject_name'] }}</strong></td>
                            <td>{{ $subject['subject_code'] }}</td>
                            <td class="text-center">{{ $subject['theory_marks'] ?? '-' }}</td>
                            <td class="text-center">{{ $subject['practical_marks'] ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ $subject['total_obtained'] }}</td>
                            <td class="text-center">{{ $subject['max_marks'] }}</td>
                            <td class="text-center">{{ $subject['percentage'] }}%</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $subject['grade'] == 'F' ? 'danger' : 'success' }}">
                                    {{ $subject['grade'] }}
                                </span>
                            </td>
                            <td>{{ $subject['remarks'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4">Total</th>
                            <th class="text-center">{{ $totalObtained }}</th>
                            <th class="text-center">{{ $totalMaxMarks }}</th>
                            <th class="text-center">{{ $overallPercentage }}%</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </div>
            </div>
            
            @if($exam->description)
                <div class="mt-4">
                    <h6>Additional Information</h6>
                    <p>{{ $exam->description }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
    }
    .info-box h6 {
        margin-bottom: 8px;
        font-size: 12px;
    }
    .info-box p {
        margin-bottom: 0;
        font-size: 16px;
        font-weight: bold;
    }
</style>
@endpush