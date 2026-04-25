{{-- resources/views/teacher/students/show.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Student Details - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-graduate me-2"></i> Student Details: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('teacher.students') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Students
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Profile Section -->
            <div class="row mb-4">
                <div class="col-md-3 text-center">
                    <img src="{{ $student->user->profile_photo_url }}" alt="{{ $student->user->name }}" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4361ee;">
                    <h4 class="mt-3">{{ $student->user->name }}</h4>
                    <p class="text-muted">{{ $student->admission_number }}</p>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6><i class="fas fa-envelope me-2 text-primary"></i> Email</h6>
                                <p>{{ $student->user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6><i class="fas fa-phone me-2 text-primary"></i> Phone</h6>
                                <p>{{ $student->user->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Date of Birth</h6>
                                <p>{{ $student->user->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6><i class="fas fa-building me-2 text-primary"></i> Class</h6>
                                <p>{{ $student->class->name }} - Section {{ $student->section->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6><i class="fas fa-hashtag me-2 text-primary"></i> Roll Number</h6>
                                <p>{{ $student->roll_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <h6><i class="fas fa-tint me-2 text-primary"></i> Blood Group</h6>
                                <p>{{ $student->user->profile?->blood_group ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-box">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address</h6>
                                <p>{{ $student->user->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Parents Information -->
            @if($student->parents->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2">Parents Information</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Relationship</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Occupation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->parents as $parent)
                                    <tr>
                                        <td>{{ $parent->name }}</td>
                                        <td>{{ ucfirst($parent->pivot->relationship) }}</td>
                                        <td>{{ $parent->email }}</td>
                                        <td>{{ $parent->phone }}</td>
                                        <td>{{ $parent->parent?->occupation ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Attendance Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Attendance Summary</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Today's Attendance</h6>
                                    @if($attendanceSummary['today'])
                                        <span class="badge bg-{{ $attendanceSummary['today']->status == 'present' ? 'success' : 'danger' }} fs-5">
                                            {{ ucfirst($attendanceSummary['today']->status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Not Marked</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>This Month</h6>
                                    <h3>{{ $attendanceSummary['monthly']['percentage'] }}%</h3>
                                    <small>{{ $attendanceSummary['monthly']['present'] }}/{{ $attendanceSummary['monthly']['total_days'] }} days</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Overall Attendance</h6>
                                    <h3>{{ $attendanceSummary['overall_percentage'] }}%</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Monthly Breakdown</h6>
                                    <small>Present: {{ $attendanceSummary['monthly']['present'] }}</small><br>
                                    <small>Absent: {{ $attendanceSummary['monthly']['absent'] }}</small><br>
                                    <small>Late: {{ $attendanceSummary['monthly']['late'] }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Exam Results -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Exam Results</h5>
                    @if(count($resultsData) > 0)
                        <div class="accordion" id="examAccordion">
                            @foreach($resultsData as $index => $exam)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                            <div class="d-flex justify-content-between w-100 me-3">
                                                <span><strong>{{ $exam['exam_name'] }}</strong> ({{ $exam['exam_date'] }})</span>
                                                <span>Total: {{ $exam['total_marks'] }}/{{ $exam['max_marks'] }} | 
                                                      Percentage: {{ $exam['percentage'] }}% | 
                                                      Grade: <span class="badge bg-{{ $exam['grade'] == 'F' ? 'danger' : 'success' }}">{{ $exam['grade'] }}</span>
                                                </span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" 
                                         data-bs-parent="#examAccordion">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
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
                                                        @foreach($exam['subjects'] as $subject)
                                                        <tr>
                                                            <td>{{ $subject['subject'] }}</td>
                                                            <td>{{ $subject['marks'] }}</td>
                                                            <td>{{ $subject['max_marks'] }}</td>
                                                            <td>{{ $subject['percentage'] }}%</td>
                                                            <td>{{ $subject['grade'] }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No exam results available.</p>
                    @endif
                </div>
            </div>
            
            <!-- Homework Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Homework Summary</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Total Submissions</h6>
                                    <h3>{{ $homeworkStats['total_submitted'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Graded Assignments</h6>
                                    <h3>{{ $homeworkStats['graded'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Average Marks</h6>
                                    <h3>{{ round($homeworkStats['average_marks'], 2) }}%</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($homeworkSubmissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Homework Title</th>
                                        <th>Subject</th>
                                        <th>Submitted On</th>
                                        <th>Marks</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($homeworkSubmissions as $submission)
                                    <tr>
                                        <td>{{ $submission->homework->title }}</td>
                                        <td>{{ $submission->homework->subject->name }}</td>
                                        <td>{{ $submission->submitted_at->format('d-m-Y h:i A') }}</td>
                                        <td>
                                            @if($submission->status == 'graded')
                                                {{ $submission->obtained_marks }}/{{ $submission->homework->total_marks }}
                                            @else
                                                Pending
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $submission->status == 'graded' ? 'success' : 'warning' }}">
                                                {{ ucfirst($submission->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No homework submissions found.</p>
                    @endif
                </div>
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
        margin-bottom: 15px;
    }
    .info-box h6 {
        margin-bottom: 8px;
    }
    .info-box p {
        margin-bottom: 0;
        color: #6c757d;
    }
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0c63e4;
    }
</style>
@endpush