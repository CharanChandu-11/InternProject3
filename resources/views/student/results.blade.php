{{-- resources/views/student/results.blade.php --}}
@extends('layouts.student')

@section('title', 'Exam Results')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i> Exam Results
                    <a href="{{ route('student.results.summary') }}" class="float-end btn btn-sm btn-outline-primary">Performance Summary</a>
                </div>
                <div class="card-body">
                    @if(count($formattedResults) > 0)
                        @foreach($formattedResults as $result)
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">{{ $result['exam_name'] }}</h5>
                                            <small class="text-muted">{{ $result['exam_date'] }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="display-6 fw-bold text-{{ $result['percentage'] >= 60 ? 'success' : ($result['percentage'] >= 40 ? 'warning' : 'danger') }}">
                                                {{ $result['percentage'] }}%
                                            </div>
                                            <span class="badge bg-{{ $result['grade'] == 'F' ? 'danger' : 'success' }} fs-6">
                                                {{ $result['grade'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th>Subject</th>
                                                    <th>Marks Obtained</th>
                                                    <th>Max Marks</th>
                                                    <th>Percentage</th>
                                                    <th>Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($result['subjects'] as $subject)
                                                    <tr>
                                                        <td class="fw-bold">{{ $subject['subject'] }}</td>
                                                        <td>{{ $subject['marks'] }}</td>
                                                        <td>{{ $subject['max_marks'] }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span>{{ $subject['percentage'] }}%</span>
                                                                <div class="progress flex-grow-1" style="height: 4px;">
                                                                    <div class="progress-bar bg-{{ $subject['percentage'] >= 60 ? 'success' : ($subject['percentage'] >= 40 ? 'warning' : 'danger') }}" 
                                                                         style="width: {{ $subject['percentage'] }}%"></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $subject['grade'] == 'F' ? 'danger' : 'success' }}">
                                                                {{ $subject['grade'] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No exam results available yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection