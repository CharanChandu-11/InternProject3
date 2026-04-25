{{-- resources/views/student/result-show.blade.php --}}
@extends('layouts.student')

@section('title', 'Exam Result Details')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt me-2"></i> {{ $exam->name }} - Result
                <a href="{{ route('student.results') }}" class="btn btn-sm btn-secondary float-end">Back</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Exam Type:</strong> {{ $exam->examType->name }}</p>
                        <p><strong>Exam Date:</strong> {{ $exam->start_date->format('d M, Y') }} - {{ $exam->end_date->format('d M, Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Marks:</strong> {{ $totalMarks }}/{{ $maxMarks }}</p>
                        <p><strong>Percentage:</strong> {{ $percentage }}%</p>
                        <p><strong>Grade:</strong> 
                            <span class="badge bg-{{ $percentage >= 40 ? 'success' : 'danger' }} fs-6">
                                {{ $this->calculateGrade($percentage) }}
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                             <tr>
                                <th>Subject</th>
                                <th>Marks Obtained</th>
                                <th>Max Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                                 <tr>
                                    <td>{{ $result->examSchedule->subject->name }}</td>
                                    <td>{{ $result->total_marks_obtained }}</td>
                                    <td>{{ $result->examSchedule->total_marks }}</td>
                                    <td>{{ $result->percentage }}%</td>
                                    <td>
                                        <span class="badge bg-{{ $result->grade != 'F' ? 'success' : 'danger' }}">
                                            {{ $result->grade }}
                                        </span>
                                    </td>
                                 </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($exam->description)
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i> {{ $exam->description }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection