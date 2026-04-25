{{-- resources/views/teacher/homework/submissions.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Homework Submissions - ' . $homework->title)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> Submissions: {{ $homework->title }}
            <div class="float-end">
                <a href="{{ route('teacher.homework.show', $homework) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Students</h6>
                            <h3>{{ $stats['total_students'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Submitted</h6>
                            <h3>{{ $stats['submitted'] }}</h3>
                            <small>{{ $stats['submission_rate'] }}%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Not Submitted</h6>
                            <h3>{{ $stats['not_submitted'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6>Late Submissions</h6>
                            <h3>{{ $stats['late'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submissions Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                            <th>Marks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissionData as $data)
                        <tr>
                            <td>{{ $data['student']->roll_number ?? '-' }}</td>
                            <td>
                                <img src="{{ $data['student']->user->profile_photo_url }}" alt="" 
                                     style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;" class="me-2">
                                {{ $data['student']->user->name }}<br>
                                <small class="text-muted">{{ $data['student']->admission_number }}</small>
                            </td>
                            <td>
                                @if($data['is_submitted'])
                                    @if($data['is_graded'])
                                        <span class="badge bg-success">Graded</span>
                                    @elseif($data['is_late'])
                                        <span class="badge bg-warning">Late</span>
                                    @else
                                        <span class="badge bg-info">Submitted</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Not Submitted</span>
                                @endif
                            </td>
                            <td>
                                @if($data['is_submitted'])
                                    {{ $data['submission']->submitted_at->format('d-m-Y h:i A') }}<br>
                                    <small>{{ $data['submission']->submitted_at->diffForHumans() }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($data['is_submitted'])
                                    @if($data['is_graded'])
                                        <strong>{{ $data['submission']->obtained_marks }}</strong> / {{ $homework->total_marks ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">Pending</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($data['is_submitted'])
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $data['submission']->id }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    @if(!$data['is_graded'])
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" 
                                                data-bs-target="#gradeModal{{ $data['submission']->id }}">
                                            <i class="fas fa-star"></i> Grade
                                        </button>
                                    @endif
                                @else
                                    <span class="text-muted">No submission</span>
                                @endif
                            </td>
                        </tr>
                        
                        <!-- View Submission Modal -->
                        @if($data['is_submitted'])
                        <div class="modal fade" id="viewModal{{ $data['submission']->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Submission Details - {{ $data['student']->user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>Submitted On:</strong> 
                                            {{ $data['submission']->submitted_at->format('d-m-Y h:i A') }}
                                        </div>
                                        <div class="mb-3">
                                            <strong>Submission Text:</strong>
                                            <p>{{ $data['submission']->submission_text ?? 'No text provided' }}</p>
                                        </div>
                                        @if($data['submission']->attachments)
                                            <div class="mb-3">
                                                <strong>Attachments:</strong>
                                                <ul>
                                                    @foreach(json_decode($data['submission']->attachments, true) ?? [] as $index => $att)
                                                        <li>
                                                            <a href="{{ route('teacher.homework.download-submission-attachment', [$data['submission']->id, $index]) }}" target="_blank">
                                                                {{ $att['name'] }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        @if($data['is_graded'])
                                            <div class="alert alert-info">
                                                <strong>Grade:</strong> {{ $data['submission']->obtained_marks }} / {{ $homework->total_marks ?? 'N/A' }}<br>
                                                <strong>Feedback:</strong> {{ $data['submission']->feedback ?? 'No feedback' }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Grade Modal -->
                        @if(!$data['is_graded'])
                        <div class="modal fade" id="gradeModal{{ $data['submission']->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('teacher.homework.grade', [$homework, $data['submission']]) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Grade Submission - {{ $data['student']->user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Marks Obtained</label>
                                                <input type="number" name="marks" class="form-control" 
                                                       max="{{ $homework->total_marks ?? 100 }}" required>
                                                <small>Max Marks: {{ $homework->total_marks ?? 100 }}</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Feedback</label>
                                                <textarea name="feedback" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Submit Grade</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection