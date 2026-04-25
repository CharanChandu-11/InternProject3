{{-- resources/views/teacher/syllabi/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Syllabus')

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-book-open me-2"></i> Syllabus
            <span class="float-end">
                <small><i class="fas fa-info-circle me-1"></i> Published syllabi for your classes</small>
            </span>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
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
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) (Current) @endif
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
                        <a href="{{ route('teacher.syllabi.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            @if($syllabi->count() > 0)
                <div class="row">
                    @foreach($syllabi as $syllabus)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm syllabus-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">{{ $syllabus->subject->name }}</h5>
                                        <span class="badge bg-success">Published</span>
                                    </div>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-building me-1"></i> {{ $syllabus->class->name }}
                                    </p>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-calendar-alt me-1"></i> {{ $syllabus->academicYear->name }}
                                    </p>
                                    <p class="card-text">{{ Str::limit($syllabus->description ?? 'No description', 80) }}</p>
                                    <div class="mt-2">
                                        <i class="fas fa-list me-1 text-primary"></i> 
                                        {{ $syllabus->topics->count() }} Topics
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('teacher.syllabi.show', $syllabus) }}" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-eye me-1"></i> View Syllabus
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $syllabi->links() }}
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-info-circle fa-3x mb-3 d-block"></i>
                    <h5>No syllabi found</h5>
                    <p class="mb-0">No published syllabi available for your classes.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .syllabus-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .syllabus-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
</style>
@endpush