{{-- resources/views/super-admin/academic-years/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Academic Year Details - ' . $academicYear->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Academic Year Details: {{ $academicYear->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.academic-years.edit', $academicYear) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.academic-years.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-tag me-2 text-primary"></i> Name</h6>
                        <p>{{ $academicYear->name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-flag-checkered me-2 text-primary"></i> Status</h6>
                        <p>
                            @if($academicYear->is_current)
                                <span class="badge bg-success">Current Academic Year</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-calendar-day me-2 text-primary"></i> Start Date</h6>
                        <p>{{ $academicYear->start_date->format('l, F j, Y') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-calendar-week me-2 text-primary"></i> End Date</h6>
                        <p>{{ $academicYear->end_date->format('l, F j, Y') }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-hourglass-half me-2 text-primary"></i> Duration</h6>
                        <p>{{ $academicYear->start_date->diffInDays($academicYear->end_date) }} days</p>
                        <small>{{ $academicYear->start_date->diffInMonths($academicYear->end_date) }} months</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-chart-line me-2 text-primary"></i> Progress</h6>
                        @php
                            $totalDays = $academicYear->start_date->diffInDays($academicYear->end_date);
                            $daysPassed = $academicYear->start_date->diffInDays(now());
                            $progress = min(100, max(0, round(($daysPassed / $totalDays) * 100)));
                        @endphp
                        <div class="d-flex align-items-center">
                            <span class="me-2">{{ $progress }}%</span>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 75 ? 'warning' : 'info') }}" 
                                     style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        <small class="text-muted">{{ $daysPassed }} days completed</small>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Classes</h6>
                            <h3>{{ $classes->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Students</h6>
                            <h3>{{ number_format($studentsCount) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6>Exams</h6>
                            <h3>{{ number_format($examsCount) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Classes in this Academic Year -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Classes in this Academic Year</h5>
                    @if($classes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Sections</th>
                                        <th>Students Count</th>
                                        <th>Class Teacher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $class)
                                    <tr>
                                        <td>{{ $class->name }}</td>
                                        <td>{{ $class->sections->pluck('name')->implode(', ') }}</td>
                                        <td>{{ $class->students_count }}</td>
                                        <td>{{ $class->classTeacher->name ?? 'Not Assigned' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No classes created for this academic year yet.</p>
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
        padding: 15px;
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
</style>
@endpush