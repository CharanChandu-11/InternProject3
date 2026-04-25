{{-- resources/views/parent/children/attendance.blade.php --}}
@extends('layouts.parent')

@section('title', 'Attendance - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-check me-2"></i> Attendance: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <a href="{{ route('parent.children.attendance.monthly', $student) }}" class="btn btn-sm btn-info">
                    <i class="fas fa-calendar-week me-1"></i> Calendar View
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Today's Attendance</h6>
                            @if($summary['today'])
                                <h3 class="mb-0">{{ ucfirst($summary['today']->status) }}</h3>
                                <small>Check In: {{ $summary['today']->check_in_time?->format('h:i A') }}</small>
                            @else
                                <h3 class="mb-0">Not Marked</h3>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Overall Attendance</h6>
                            <h3 class="mb-0">{{ $summary['overall_percentage'] }}%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Days</h6>
                            <h3 class="mb-0">{{ $attendances->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="month" class="form-select">
                            <option value="">All Months</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="year" class="form-select">
                            <option value="">All Years</option>
                            @for($y = date('Y')-2; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('parent.children.attendance', $student) }}" class="btn btn-secondary w-100">Reset</a>
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