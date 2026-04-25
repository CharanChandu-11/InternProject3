{{-- resources/views/student/attendance/monthly.blade.php --}}
@extends('layouts.student')

@section('title', 'Monthly Attendance - ' . Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'))

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-week me-2"></i> 
            Monthly Attendance: {{ Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
            <div class="float-end">
                <a href="{{ route('student.attendance') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-list me-1"></i> List View
                </a>
                <a href="{{ route('student.attendance.yearly') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-chart-line me-1"></i> Yearly Summary
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Month Selector -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="month" class="form-select">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="year" class="form-select">
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">View</button>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('student.attendance.monthly') }}" class="btn btn-secondary">Current Month</a>
                    </div>
                </div>
            </form>
            
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Present</h6>
                            <h3>{{ $stats['present'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6>Absent</h6>
                            <h3>{{ $stats['absent'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Late</h6>
                            <h3>{{ $stats['late'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Half Day</h6>
                            <h3>{{ $stats['half_day'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Rate -->
            <div class="alert alert-primary mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Attendance Rate:</strong> {{ $stats['attendance_rate'] }}%
                        <br>
                        <small>{{ $stats['present'] }} out of {{ $stats['expected_days'] }} working days</small>
                    </div>
                    <div class="progress" style="width: 200px; height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['attendance_rate'] }}%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar -->
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Sunday</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($calendar as $week)
                            <tr>
                                @foreach($week as $day)
                                    @if($day)
                                        <td class="{{ $day['is_weekend'] ? 'bg-light' : '' }}" style="vertical-align: top;">
                                            <div class="fw-bold mb-2">{{ $day['day'] }}</div>
                                            @if($day['status'])
                                                @if($day['status'] == 'present')
                                                    <span class="badge bg-success">P</span>
                                                @elseif($day['status'] == 'absent')
                                                    <span class="badge bg-danger">A</span>
                                                @elseif($day['status'] == 'late')
                                                    <span class="badge bg-warning">L</span>
                                                @else
                                                    <span class="badge bg-info">H</span>
                                                @endif
                                                <div class="small mt-1">
                                                    <small>{{ $day['check_in'] ?? '' }}</small>
                                                </div>
                                            @elseif(!$day['is_weekend'])
                                                <span class="badge bg-secondary">-</span>
                                                <div class="small mt-1">
                                                    <small>Not marked</small>
                                                </div>
                                            @endif
                                            @if($day['remarks'])
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-comment"></i>
                                                </div>
                                            @endif
                                        </td>
                                    @else
                                        <td class="bg-light"></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Legend -->
            <div class="mt-4">
                <h6>Legend</h6>
                <div class="d-flex gap-3">
                    <span><span class="badge bg-success">P</span> = Present</span>
                    <span><span class="badge bg-danger">A</span> = Absent</span>
                    <span><span class="badge bg-warning">L</span> = Late</span>
                    <span><span class="badge bg-info">H</span> = Half Day</span>
                    <span><span class="badge bg-secondary">-</span> = Not Marked</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection