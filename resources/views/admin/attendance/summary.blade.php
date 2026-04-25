{{-- resources/views/admin/attendance/summary.blade.php --}}
@extends('layouts.admin')

@section('title', 'Attendance Summary')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-bar me-2"></i> Attendance Summary Report
            <div class="float-end">
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Records
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="month" class="form-select">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="year" class="form-control" placeholder="Year" value="{{ $year }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.attendance.summary') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Students</h6>
                            <h2 class="mb-0">{{ $classSummary['total_students'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Present Days</h6>
                            <h2 class="mb-0">{{ $classSummary['total_present'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6 class="card-title">Absent Days</h6>
                            <h2 class="mb-0">{{ $classSummary['total_absent'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Average Attendance</h6>
                            <h2 class="mb-0">{{ $classSummary['average_percentage'] }}%</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Half Day</th>
                            <th>Total Days</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceData as $data)
                            @php
                                $percentage = $data['percentage'];
                                $statusColor = $percentage >= 75 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
                                $statusText = $percentage >= 75 ? 'Good' : ($percentage >= 60 ? 'Average' : 'Needs Improvement');
                            @endphp
                            <tr>
                                <td>{{ $data['student']->roll_number }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $data['student']->user->profile_photo_url }}" 
                                             alt="" class="rounded-circle me-2" 
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                        {{ $data['student']->user->name }}
                                    </div>
                                </td>
                                <td>{{ $data['student']->admission_number }}</td>
                                <td>{{ $data['student']->class->name ?? 'N/A' }} - {{ $data['student']->section->name ?? '' }}</td>
                                <td>{{ $data['present'] }}</td>
                                <td>{{ $data['absent'] }}</td>
                                <td>{{ $data['late'] }}</td>
                                <td>{{ $data['half_day'] }}</td>
                                <td>{{ $data['total_days'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{ $percentage }}%</span>
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <div class="progress-bar bg-{{ $statusColor }}" 
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Export Button -->
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-success" onclick="exportReport()">
                    <i class="fas fa-download me-1"></i> Export Report
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function exportReport() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = "{{ route('admin.attendance.export') }}?" + params.toString();
    }
</script>
@endpush