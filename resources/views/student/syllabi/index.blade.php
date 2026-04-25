{{-- resources/views/student/syllabi/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Syllabus')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Syllabus
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('student.syllabi.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            @if($syllabi->count() > 0)
                <div class="row">
                    @foreach($syllabi as $syllabus)
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title">{{ $syllabus->subject->name }}</h5>
                                        <span class="badge bg-success">{{ $syllabus->subject->code }}</span>
                                    </div>
                                    <p class="card-text text-muted small">{{ $syllabus->description ?? 'No description' }}</p>
                                    <div class="mt-2">
                                        <i class="fas fa-calendar-alt me-1"></i> Academic Year: {{ $syllabus->academicYear->name }}
                                    </div>
                                    <div class="mt-2">
                                        <i class="fas fa-list me-1"></i> Topics: {{ $syllabus->topics->count() }}
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="{{ route('student.syllabi.show', $syllabus) }}" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-eye me-1"></i> View Syllabus
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{ $syllabi->links() }}
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> No syllabus available for your class.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection