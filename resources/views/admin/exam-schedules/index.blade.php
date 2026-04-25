{{-- resources/views/admin/exam-schedules/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Exam Schedules')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Exam Schedule Management
            <div class="float-end">
                <a href="{{ route('admin.exam-schedules.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Schedule
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="exam_id" class="form-select">
                            <option value="">All Exams</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Marks</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->exam->name }}</td>
                            <td>{{ $schedule->class->name }}</td>
                            <td>{{ $schedule->section->name }}</td>
                            <td>{{ $schedule->subject->name }}</td>
                            <td>{{ $schedule->exam_date->format('M d, Y') }}</td>
                            <td>{{ date('h:i A', strtotime($schedule->start_time)) }} - {{ date('h:i A', strtotime($schedule->end_time)) }}</td>
                            <td>{{ $schedule->total_marks }}</td>
                            <td>{{ $schedule->room_number ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.exam-schedules.show', $schedule) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.exam-schedules.edit', $schedule) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.exam-schedules.destroy', $schedule) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this schedule?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.exam-results.bulk', $schedule) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-edit"></i> Marks
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $schedules->links() }}
        </div>
    </div>
</div>
@endsection