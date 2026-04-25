{{-- resources/views/teacher/students-show.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Student Profile - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}" 
                         class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--primary);">
                    <h4>{{ $student->user->name }}</h4>
                    <p class="text-muted">{{ $student->user->email }}</p>
                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Roll No</small>
                                <h6 class="mb-0">{{ $student->roll_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Admission No</small>
                                <h6 class="mb-0">{{ $student->admission_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Class</small>
                                <h6 class="mb-0">{{ $student->class->name }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Section</small>
                                <h6 class="mb-0">{{ $student->section->name }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">DOB</small>
                                <h6 class="mb-0">{{ $student->user->profile?->date_of_birth?->format('d M, Y') ?? 'N/A' }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Phone</small>
                                <h6 class="mb-0">{{ $student->user->phone ?? 'N/A' }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Attendance Summary
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h2 class="text-{{ $attendanceSummary['overall_percentage'] >= 75 ? 'success' : ($attendanceSummary['overall_percentage'] >= 60 ? 'warning' : 'danger') }}">
                                    {{ $attendanceSummary['overall_percentage'] }}%
                                </h2>
                                <p>Overall Attendance</p>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $attendanceSummary['overall_percentage'] >= 75 ? 'success' : ($attendanceSummary['overall_percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $attendanceSummary['overall_percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2">
                                        <h4>{{ $attendanceSummary['monthly_total'] }}</h4>
                                        <small>Days in Month</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-2">
                                        <h4 class="text-success">{{ $attendanceSummary['monthly_present'] }}</h4>
                                        <small>Present</small>
                                    </div>
                                </div>
                                <div class="col-6 mt-2">
                                    <div class="bg-light rounded-3 p-2">
                                        <h4 class="text-danger">{{ $attendanceSummary['monthly_absent'] }}</h4>
                                        <small>Absent</small>
                                    </div>
                                </div>
                                <div class="col-6 mt-2">
                                    <div class="bg-light rounded-3 p-2">
                                        <h4>{{ $attendanceSummary['monthly_percentage'] }}%</h4>
                                        <small>This Month</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2"></i> Exam Results
                </div>
                <div class="card-body">
                    @if($examResults->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Exam</th>
                                        <th>Subject</th>
                                        <th>Marks</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examResults as $examId => $results)
                                        @foreach($results as $result)
                                            <tr>
                                                <td>{{ $result->examSchedule->exam->name }}</td>
                                                <td>{{ $result->examSchedule->subject->name }}</td>
                                                <td>{{ $result->total_marks_obtained }}/{{ $result->examSchedule->total_marks }}</td>
                                                <td>{{ $result->percentage }}%</td>
                                                <td>
                                                    <span class="badge bg-{{ $result->grade != 'F' ? 'success' : 'danger' }}">
                                                        {{ $result->grade }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No exam results available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection