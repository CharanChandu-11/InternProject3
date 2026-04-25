{{-- resources/views/teacher/exams/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Exam Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-file-alt me-2"></i> Exam Management
            <div class="float-end">
                <a href="{{ route('teacher.exams.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> Schedule New Exam
                </a>
                <a href="{{ route('teacher.exams.upcoming') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-calendar-alt me-1"></i> Upcoming Exams
                </a>
                <a href="{{ route('teacher.exams.results') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-chart-bar me-1"></i> View Results
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="exam_id" class="form-select">
                            <option value="">All Exams</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('teacher.exams.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Exam Schedules Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Exam Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Exam Date</th>
                            <th>Time</th>
                            <th>Max Marks</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examSchedules as $schedule)
                        @php
                            $today = \Carbon\Carbon::today();
                            $examDate = $schedule->exam_date;
                            $isCompleted = $examDate < $today;
                            $isToday = $examDate == $today;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $schedule->exam->name }}</strong><br>
                                <small class="text-muted">{{ $schedule->exam->examType->name ?? '' }}</small>
                            </td>
                            <td>{{ $schedule->class->name }}</td>
                            <td>{{ $schedule->section->name }}</td>
                            <td>{{ $schedule->subject->name }} ({{ $schedule->subject->code }})</span></td>
                            <td>
                                {{ $schedule->exam_date->format('d-m-Y') }}<br>
                                <small class="text-muted">{{ $schedule->exam_date->format('l') }}</small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - 
                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                            </td>
                            <td>
                                @php
                                    $theoryMarks = $schedule->total_marks ?? 0;
                                    $practicalMarks = $schedule->practical_marks ?? 0;
                                    $totalMarks = $theoryMarks + $practicalMarks;
                                @endphp
                                {{ $totalMarks }}
                                @if($practicalMarks > 0)
                                    <br><small>(Theory: {{ $theoryMarks }}, Practical: {{ $practicalMarks }})</small>
                                @endif
                            </td>
                            <td>
                                @if($isCompleted)
                                    <span class="badge bg-secondary">Completed</span>
                                @elseif($isToday)
                                    <span class="badge bg-warning text-dark">Today</span>
                                @else
                                    <span class="badge bg-success">Upcoming</span>
                                @endif
                            </td>
                            <td>
                                @if($isCompleted)
                                    <a href="{{ route('teacher.exams.results') }}?exam_id={{ $schedule->exam_id }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-chart-line"></i> Results
                                    </a>
                                @else
                                    <a href="{{ route('teacher.exams.students', $schedule) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Enter Marks
                                    </a>
                                @endif
                                <a href="{{ route('teacher.exams.edit', $schedule) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <a href="{{ route('teacher.exams.export', $schedule) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('teacher.exams.destroy', $schedule) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            {{ $schedule->results()->count() > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            
            {{ $examSchedules->links() }}
        </div>
    </div>
</div>
@endsection