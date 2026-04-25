{{-- resources/views/admin/exam-results/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Exam Results')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line me-2"></i> Exam Results
            <div class="float-end">
                <a href="{{ route('admin.exam-results.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Result
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="exam_id" class="form-select">
                            <option value="">All Exams</option>
                            @foreach($examSchedules->unique('exam_id') as $schedule)
                                <option value="{{ $schedule->exam_id }}" {{ request('exam_id') == $schedule->exam_id ? 'selected' : '' }}>
                                    {{ $schedule->exam->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach(\App\Models\Classes::all() as $class)
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
                        <a href="{{ route('admin.exam-results.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $result->student->user->profile_photo_url }}" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                                    <span>{{ $result->student->user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $result->examSchedule->exam->name }}</td>
                            <td>{{ $result->examSchedule->subject->name }}</td>
                            <td>{{ $result->total_marks_obtained }}/{{ $result->examSchedule->total_marks }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $result->percentage }}%</span>
                                    <div class="progress flex-grow-1" style="height: 5px;">
                                        <div class="progress-bar bg-{{ $result->percentage >= 75 ? 'success' : ($result->percentage >= 60 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $result->percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-{{ $result->grade == 'F' ? 'danger' : 'success' }}">{{ $result->grade }}</span></td>
                            <td>
                                <a href="{{ route('admin.exam-results.show', $result) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.exam-results.edit', $result) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.exam-results.destroy', $result) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this result?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $results->links() }}
        </div>
    </div>
</div>
@endsection