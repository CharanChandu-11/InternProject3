{{-- resources/views/teacher/attendance/summary.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Attendance Summary')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-bar me-2"></i> Attendance Summary Report
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <select name="month" class="form-select">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="year" class="form-select">
                            @for($y = date('Y')-2; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="class_id" class="form-select" id="classSelect">
                            <option value="">All Classes</option>
                            @foreach($classSections as $cs)
                                <option value="{{ $cs['class_id'] }}" {{ request('class_id') == $cs['class_id'] ? 'selected' : '' }}>
                                    {{ $cs['class_name'] }} {{ $cs['section_name'] ? '- Section ' . $cs['section_name'] : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                    </div>
                </div>
            </form>
            
            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Students</h6>
                            <h2>{{ $summaryStats['total_students'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Working Days</h6>
                            <h2>{{ $summaryStats['total_days'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Overall Present</h6>
                            <h2>{{ $summaryStats['overall_present'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Overall Percentage</h6>
                            <h2>{{ $summaryStats['overall_percentage'] }}%</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="mb-3">
                <span class="badge bg-success">Present</span>
                <span class="badge bg-danger ms-2">Absent</span>
                <span class="badge bg-warning ms-2">Late</span>
                <span class="badge bg-info ms-2">Half Day</span>
                <span class="badge bg-secondary ms-2">No Data</span>
            </div>
            
            <!-- Attendance Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Roll No</th>
                            @foreach($calendarDays as $day)
                                @if($day['is_working_day'])
                                    <th class="text-center">
                                        {{ $day['date']->format('d') }}<br>
                                        <small>{{ $day['day_name'] }}</small>
                                    </th>
                                @endif
                            @endforeach
                            <th>Present</th>
                            <th>Absent</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceData as $data)
                        <tr>
                            <td>
                                <img src="{{ $data['student']->user->profile_photo_url }}" alt="" 
                                     style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;" class="me-2">
                                {{ $data['student']->user->name }}
                            </td>
                            <td>{{ $data['student']->class->name }}</td>
                            <td>{{ $data['student']->section->name }}</td>
                            <td>{{ $data['student']->roll_number ?? '-' }}</td>
                            @foreach($calendarDays as $day)
                                @if($day['is_working_day'])
                                    @php
                                        $dateStr = $day['date']->format('Y-m-d');
                                        $attRecord = $data['records'][$dateStr] ?? null;
                                        $status = $attRecord ? $attRecord->status : 'none';
                                    @endphp
                                    <td class="text-center">
                                        @if($status == 'present')
                                            <span class="badge bg-success">P</span>
                                        @elseif($status == 'absent')
                                            <span class="badge bg-danger">A</span>
                                        @elseif($status == 'late')
                                            <span class="badge bg-warning">L</span>
                                        @elseif($status == 'half_day')
                                            <span class="badge bg-info">H</span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                            <td><strong>{{ $data['present'] }}</strong></td>
                            <td>{{ $data['absent'] + $data['late'] + $data['half_day'] }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-1">{{ $data['percentage'] }}%</span>
                                    <div class="progress" style="width: 50px; height: 5px;">
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
</div>
@endsection

@push('scripts')
<script>
    $('#classSelect').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/teacher/students/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionSelect');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionSelect').empty().append('<option value="">All Sections</option>');
        }
    });
</script>
@endpush