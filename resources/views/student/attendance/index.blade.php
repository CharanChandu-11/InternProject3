{{-- resources/views/student/attendance/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Attendance Records')

@section('content')
<div class="animate-fadeInUp">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Today's Attendance</h6>
                    @if($summary['today']['status'] == 'present')
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <h3 class="mb-0">Present</h3>
                    @elseif($summary['today']['status'] == 'absent')
                        <i class="fas fa-times-circle fa-3x mb-2"></i>
                        <h3 class="mb-0">Absent</h3>
                    @elseif($summary['today']['status'] == 'late')
                        <i class="fas fa-clock fa-3x mb-2"></i>
                        <h3 class="mb-0">Late</h3>
                    @elseif($summary['today']['status'] == 'half_day')
                        <i class="fas fa-sun fa-3x mb-2"></i>
                        <h3 class="mb-0">Half Day</h3>
                    @else
                        <i class="fas fa-question-circle fa-3x mb-2"></i>
                        <h3 class="mb-0">Not Marked</h3>
                    @endif
                    @if($summary['today']['check_in'])
                        <small class="d-block mt-2">Check In: {{ $summary['today']['check_in'] }}</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">This Month</h6>
                    <h3 class="mb-0">{{ $summary['current_month']['percentage'] }}%</h3>
                    <small>{{ $summary['current_month']['present'] }} / {{ $summary['current_month']['total'] }} days present</small>
                    <div class="progress mt-2 bg-light" style="height: 5px;">
                        <div class="progress-bar bg-white" style="width: {{ $summary['current_month']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">Overall Attendance</h6>
                    <h3 class="mb-0">{{ $summary['overall']['percentage'] }}%</h3>
                    <small>{{ $summary['overall']['present'] }} / {{ $summary['overall']['total'] }} days present</small>
                    <div class="progress mt-2 bg-light" style="height: 5px;">
                        <div class="progress-bar bg-white" style="width: {{ $summary['overall']['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Attendance Records
            <div class="float-end">
                <a href="{{ route('student.attendance.monthly') }}" class="btn btn-sm btn-info me-1">
                    <i class="fas fa-calendar-week me-1"></i> Calendar View
                </a>
                <a href="{{ route('student.attendance.yearly') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-chart-line me-1"></i> Yearly Summary
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <select name="year" class="form-select">
                            <option value="">All Years</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="month" class="form-select">
                            <option value="">All Months</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" placeholder="From Date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" placeholder="To Date" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('student.attendance') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Attendance Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->attendance_date->format('d-m-Y') }}</td>
                            <td>{{ $attendance->attendance_date->format('l') }}</td>
                            <td>
                                @if($attendance->status == 'present')
                                    <span class="badge bg-success">Present</span>
                                @elseif($attendance->status == 'absent')
                                    <span class="badge bg-danger">Absent</span>
                                @elseif($attendance->status == 'late')
                                    <span class="badge bg-warning">Late</span>
                                @else
                                    <span class="badge bg-info">Half Day</span>
                                @endif
                            </td>
                            <td>{{ $attendance->check_in_time?->format('h:i A') ?? '-' }}</td>
                            <td>{{ $attendance->check_out_time?->format('h:i A') ?? '-' }}</td>
                            <td>{{ $attendance->remarks ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection