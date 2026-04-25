{{-- resources/views/teacher/attendance/history.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Attendance History')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history me-2"></i> Attendance History
            <div class="float-end">
                <a href="{{ route('teacher.attendance.summary') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-chart-bar me-1"></i> Summary
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classSections as $cs)
                                <option value="{{ $cs['class_id'] }}" {{ request('class_id') == $cs['class_id'] ? 'selected' : '' }}>
                                    {{ $cs['class_name'] }} {{ $cs['section_name'] ? '- Section ' . $cs['section_name'] : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="student_id" class="form-select" id="studentFilter">
                            <option value="">All Students</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->user->name }}
                                </option>
                            @endforeach
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
                        <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('teacher.attendance.history') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Attendance Table -->
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
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td>
                                <img src="{{ $attendance->attendable->user->profile_photo_url }}" alt="" 
                                     style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;" class="me-2">
                                {{ $attendance->attendable->user->name }}<br>
                                <small class="text-muted">{{ $attendance->attendable->admission_number }}</small>
                            </td>
                            <td>{{ $attendance->attendable->class->name ?? 'N/A' }}</td>
                            <td>{{ $attendance->attendable->section->name ?? 'N/A' }}</td>
                            <td>{{ $attendance->attendance_date->format('d-m-Y') }}<br>
                                <small>{{ $attendance->attendance_date->format('l') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'absent' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td>{{ $attendance->check_in_time?->format('h:i A') ?? '-' }}</td>
                            <td>{{ $attendance->remarks ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#classFilter').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/teacher/students/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionFilter');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                    
                    // Load students for this class
                    $.ajax({
                        url: '/teacher/students/by-class/' + classId,
                        type: 'GET',
                        success: function(studentData) {
                            var studentSelect = $('#studentFilter');
                            studentSelect.empty();
                            studentSelect.append('<option value="">All Students</option>');
                            $.each(studentData, function(key, student) {
                                studentSelect.append('<option value="' + student.id + '">' + student.name + '</option>');
                            });
                        }
                    });
                }
            });
        } else {
            $('#sectionFilter').empty().append('<option value="">All Sections</option>');
            $('#studentFilter').empty().append('<option value="">All Students</option>');
        }
    });
</script>
@endpush