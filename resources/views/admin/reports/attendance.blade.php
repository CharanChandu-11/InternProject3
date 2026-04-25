{{-- resources/views/admin/reports/attendance.blade.php --}}
@extends('layouts.admin')

@section('title', 'Attendance Report')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-check me-2"></i> Attendance Report
            <div class="float-end">
                <button class="btn btn-sm btn-success" id="exportExcel">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
                <button class="btn btn-sm btn-danger" id="exportPDF">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" id="sectionFilter">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('admin.reports.attendance') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Records</h6>
                            <h3>{{ number_format($summary['total_records']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Present</h6>
                            <h3>{{ number_format($summary['present']) }} ({{ $summary['present_percent'] }}%)</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6>Absent</h6>
                            <h3>{{ number_format($summary['absent']) }} ({{ $summary['absent_percent'] }}%)</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Late / Half Day</h6>
                            <h3>{{ number_format($summary['late'] + $summary['half_day']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-line me-2"></i> Daily Attendance Trend
                        </div>
                        <div class="card-body">
                            <canvas id="attendanceChart" height="300" class="h-100 w-100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Class-wise Breakdown -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar me-2"></i> Class-wise Attendance
                        </div>
                        <div class="card-body">
                            <canvas id="classChart" height="250" class="h-100 w-100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-table me-2"></i> Class-wise Breakdown
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Total Records</th>
                                            <th>Present</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classWise as $class)
                                            <tr>
                                                <td>{{ $class['class'] }}</td>
                                                <td>{{ number_format($class['total']) }}</td>
                                                <td>{{ number_format($class['present']) }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2">{{ $class['percentage'] }}%</span>
                                                        <div class="progress flex-grow-1" style="height: 5px;">
                                                            <div class="progress-bar bg-success" style="width: {{ $class['percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Breakdown Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-day me-2"></i> Daily Breakdown
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Total Students</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyData as $data)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($data['date'])->format('d-m-Y') }}</td>
                                        <td>{{ $data['day'] }}</td>
                                        <td>{{ number_format($data['total']) }}</td>
                                        <td>{{ number_format($data['present']) }}</td>
                                        <td>{{ number_format($data['absent']) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $data['percentage'] }}%</span>
                                                <div class="progress flex-grow-1" style="height: 5px;">
                                                    <div class="progress-bar bg-{{ $data['percentage'] >= 75 ? 'success' : ($data['percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                                         style="width: {{ $data['percentage'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detailed Records -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> Detailed Records
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                <tr>
                                    <td>
                                        <img src="{{ $attendance->attendable->user->profile_photo_url }}" alt="" 
                                             style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;" class="me-2">
                                        {{ $attendance->attendable->user->name }}
                                     </span></td>
                                    <td>{{ $attendance->attendable->class->name ?? 'N/A' }}</span></td>
                                    <td>{{ $attendance->attendable->section->name ?? 'N/A' }}</span></td>
                                    <td>{{ $attendance->attendance_date->format('d-m-Y') }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'absent' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                     </span></td>
                                    <td>{{ $attendance->check_in_time?->format('h:i A') ?? '-' }}</span></td>
                                    <td>{{ $attendance->check_out_time?->format('h:i A') ?? '-' }}</span></td>
                                    <td>{{ $attendance->remarks ?? '-' }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Attendance Chart
    const dailyCtx = document.getElementById('attendanceChart').getContext('2d');
    const dailyLabels = @json(collect($dailyData)->pluck('date')->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('d M');
    }));
    const dailyPresent = @json(collect($dailyData)->pluck('present'));
    const dailyAbsent = @json(collect($dailyData)->pluck('absent'));
    
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Present',
                    data: dailyPresent,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Absent',
                    data: dailyAbsent,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
    
    // Class-wise Chart
    const classCtx = document.getElementById('classChart').getContext('2d');
    const classLabels = @json(collect($classWise)->pluck('class'));
    const classPercentages = @json(collect($classWise)->pluck('percentage'));
    
    new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: classLabels,
            datasets: [{
                label: 'Attendance Percentage (%)',
                data: classPercentages,
                backgroundColor: classPercentages.map(p => p >= 75 ? '#28a745' : (p >= 60 ? '#ffc107' : '#dc3545')),
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Percentage (%)' }
                }
            }
        }
    });
    
    // Dynamic section loading
    $('#classFilter').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/admin/sections/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionFilter');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionFilter').empty().append('<option value="">All Sections</option>');
        }
    });
</script>
@endpush