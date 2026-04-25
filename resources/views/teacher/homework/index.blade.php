{{-- resources/views/teacher/homework/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Homework Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Homework Management
            <div class="float-end">
                <a href="{{ route('teacher.homework.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Assign Homework
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
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
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
                        <a href="{{ route('teacher.homework.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Homework Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Submissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($homeworks as $homework)
                        <tr>
                            <td>
                                <strong>{{ $homework->title }}</strong><br>
                                <small class="text-muted">{{ Str::limit($homework->description, 50) }}</small>
                            </td>
                            <td>{{ $homework->class->name }}</td>
                            <td>{{ $homework->section->name }}</td>
                            <td>{{ $homework->subject->name }} ({{ $homework->subject->code }})</td>
                            <td>
                                {{ $homework->submission_date->format('d-m-Y') }}<br>
                                <small class="text-{{ $homework->submission_date->isPast() ? 'danger' : 'success' }}">
                                    {{ $homework->submission_date->diffForHumans() }}
                                </small>
                            </td>
                            <td>
                                @if($homework->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($homework->status == 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @else
                                    <span class="badge bg-danger">Expired</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $submittedCount = $homework->submissions()->where('status', 'submitted')->count();
                                    $gradedCount = $homework->submissions()->where('status', 'graded')->count();
                                @endphp
                                <span class="badge bg-info">{{ $submittedCount }} submitted</span>
                                <span class="badge bg-success">{{ $gradedCount }} graded</span>
                            </td>
                            <td>
                                <a href="{{ route('teacher.homework.show', $homework) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('teacher.homework.edit', $homework) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('teacher.homework.submissions', $homework) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-users"></i>
                                </a>
                                <form action="{{ route('teacher.homework.destroy', $homework) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $homeworks->links() }}
        </div>
    </div>
</div>
@endsection