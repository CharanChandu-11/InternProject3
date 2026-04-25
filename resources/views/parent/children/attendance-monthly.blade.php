{{-- resources/views/parent/children/attendance-monthly.blade.php --}}
@extends('layouts.parent')

@section('title', 'Monthly Attendance - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-week me-2"></i> Monthly Attendance: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.attendance', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-list me-1"></i> List View
                </a>
            </div>
        </div>
        <div class="card-body">
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
                            @for($y = date('Y')-2; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">View</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('parent.children.attendance.monthly', $student) }}" class="btn btn-secondary w-100">Current</a>
                    </div>
                </div>
            </form>
            
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Present</h6>
                            <h3>{{ $stats['present'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6>Absent</h6>
                            <h3>{{ $stats['absent'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Attendance Rate</h6>
                            <h3>{{ $stats['attendance_rate'] }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar -->
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($calendar as $week)
                            <tr>
                                @foreach($week as $day)
                                    <td class="{{ $day && $day['status'] ? 'table-' . ($day['status'] == 'present' ? 'success' : ($day['status'] == 'absent' ? 'danger' : 'warning')) : '' }}">
                                        @if($day)
                                            <div class="fw-bold">{{ $day['day'] }}</div>
                                            @if($day['status'])
                                                <span class="badge bg-{{ $day['status'] == 'present' ? 'success' : ($day['status'] == 'absent' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst(substr($day['status'], 0, 1)) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            
            <div class="mt-3">
                <small class="text-muted">Legend: P=Present, A=Absent, L=Late, H=Half Day</small>
            </div>
        </div>
    </div>
</div>
@endsection