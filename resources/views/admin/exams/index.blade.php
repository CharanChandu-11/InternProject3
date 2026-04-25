{{-- resources/views/admin/exams/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Exams')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-file-alt me-2"></i> Exam Management
            <div class="float-end">
                <a href="{{ route('admin.exams.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Exam
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="exam_type_id" class="form-select">
                            <option value="">All Types</option>
                            @foreach($examTypes as $type)
                                <option value="{{ $type->id }}" {{ request('exam_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Academic Year</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                        <tr>
                            <td class="fw-bold">{{ $exam->name }}</td>
                            <td>{{ $exam->examType->name }}</td>
                            <td>{{ $exam->academicYear->name }}</td>
                            <td>{{ $exam->start_date->format('M d, Y') }}</td>
                            <td>{{ $exam->end_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $exam->status == 'upcoming' ? 'info' : ($exam->status == 'ongoing' ? 'warning' : 'success') }}">
                                    {{ ucfirst($exam->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this exam?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <a href="#" class="btn btn-sm btn-success">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $exams->links() }}
        </div>
    </div>
</div>
@endsection