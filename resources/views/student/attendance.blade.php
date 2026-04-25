{{-- resources/views/student/attendance.blade.php --}}
@extends('layouts.student')

@section('title', 'Attendance')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Attendance Overview
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-5 text-center mb-4 mb-lg-0">
                            <canvas id="attendanceChart" style="max-width: 300px; margin: 0 auto;"></canvas>
                            <div class="mt-3">
                                <h2 class="mb-0">{{ $percentage }}%</h2>
                                <p class="text-muted">Overall Attendance</p>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-4">
                                    <div class="bg-success bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                        <h3 class="mb-0">{{ $presentDays }}</h3>
                                        <small class="text-muted">Present Days</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="bg-danger bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-times-circle text-danger fa-2x mb-2"></i>
                                        <h3 class="mb-0">{{ $absentDays }}</h3>
                                        <small class="text-muted">Absent Days</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="bg-warning bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                                        <h3 class="mb-0">{{ $attendances->where('status', 'late')->count() }}</h3>
                                        <small class="text-muted">Late Arrivals</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt"></i> Attendance Records
                    <div class="float-end">
                        <a href="{{ route('student.attendance.monthly') }}" class="btn btn-sm btn-outline-primary me-2">Monthly View</a>
                        <a href="{{ route('student.attendance.yearly') }}" class="btn btn-sm btn-outline-primary">Yearly View</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
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
                                        <td class="fw-bold">{{ $attendance->attendance_date->format('d M, Y') }}</td>
                                        <td>{{ $attendance->attendance_date->format('l') }}</td>
                                        <td>
                                            @if($attendance->status == 'present')
                                                <span class="badge bg-success">Present</span>
                                            @elseif($attendance->status == 'absent')
                                                <span class="badge bg-danger">Absent</span>
                                            @elseif($attendance->status == 'late')
                                                <span class="badge bg-warning">Late</span>
                                            @else
                                                <span class="badge bg-info">{{ ucfirst($attendance->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $attendance->check_in_time?->format('h:i A') ?? '--' }}</td>
                                        <td>{{ $attendance->check_out_time?->format('h:i A') ?? '--' }}</td>
                                        <td>{{ $attendance->remarks ?? '--' }}</td>
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
<script>
    var ctx = document.getElementById('attendanceChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late'],
            datasets: [{
                data: [{{ $presentDays }}, {{ $absentDays }}, {{ $attendances->where('status', 'late')->count() }}],
                backgroundColor: ['#06ffa5', '#ef476f', '#ffd166'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            cutout: '65%'
        }
    });
</script>
@endpush