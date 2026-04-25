{{-- resources/views/student/attendance-monthly.blade.php --}}
@extends('layouts.student')

@section('title', 'Monthly Attendance')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-alt me-2"></i> Attendance - {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
                <a href="{{ route('student.attendance') }}" class="btn btn-sm btn-secondary float-end">Back</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3>{{ $summary['percentage'] }}%</h3>
                                <p class="text-muted">Attendance Percentage</p>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: {{ $summary['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h4>{{ $summary['present'] }}</h4>
                                        <small>Present</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h4>{{ $summary['absent'] }}</h4>
                                        <small>Absent</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h4>{{ $summary['late'] }}</h4>
                                        <small>Late</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                             <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Status</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($calendar as $day)
                                <tr>
                                    <td>{{ $day['date']->format('d M, Y') }}</td>
                                    <td>{{ $day['date']->format('l') }}</td>
                                    <td>
                                        @if($day['status'] == 'present')
                                            <span class="badge bg-success">Present</span>
                                        @elseif($day['status'] == 'absent')
                                            <span class="badge bg-danger">Absent</span>
                                        @elseif($day['status'] == 'late')
                                            <span class="badge bg-warning">Late</span>
                                        @else
                                            <span class="badge bg-secondary">Not Marked</span>
                                        @endif
                                    </td>
                                    <td>{{ $day['check_in'] ?? '--' }}</td>
                                    <td>{{ $day['check_out'] ?? '--' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection