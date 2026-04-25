{{-- resources/views/parent/children/result-detail.blade.php --}}
@extends('layouts.parent')

@section('title', 'Result Detail - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line me-2"></i> Result Details: {{ $exam->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.results', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <h6>Student Name</h6>
                        <p>{{ $student->user->name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6>Exam Name</h6>
                        <p>{{ $exam->name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6>Exam Date</h6>
                        <p>{{ $exam->start_date->format('F j, Y') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6>Exam Type</h6>
                        <p>{{ $exam->examType->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            
            <h5 class="mt-4">Subject-wise Performance</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Theory</th>
                            <th>Practical</th>
                            <th>Total</th>
                            <th>Max Marks</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                        <tr>
                            <td>{{ $result->examSchedule->subject->name }}</span></td>
                            <td>{{ $result->theory_marks_obtained ?? '-' }}</span></td>
                            <td>{{ $result->practical_marks_obtained ?? '-' }}</span></td>
                            <td><strong>{{ $result->total_marks_obtained }}</strong></span></td>
                            <td>{{ $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0) }}</span></td>
                            <td>{{ $result->percentage }}%</span></td>
                            <td>
                                <span class="badge bg-{{ $result->grade == 'F' ? 'danger' : 'success' }}">
                                    {{ $result->grade }}
                                </span>
                             </span></td>
                            <td>{{ $result->remarks ?? '-' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            
            @php
                $totalObtained = $results->sum('total_marks_obtained');
                $totalMax = $results->sum(function($r) {
                    return $r->examSchedule->total_marks + ($r->examSchedule->practical_marks ?? 0);
                });
                $overallPercentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
            @endphp
            <div class="alert alert-info mt-3">
                <strong>Total:</strong> {{ $totalObtained }} / {{ $totalMax }} ({{ $overallPercentage }}%)
            </div>
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
    }
</style>
@endpush