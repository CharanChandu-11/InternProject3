{{-- resources/views/super-admin/attendance/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Attendance Records')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-check me-2"></i> Attendance Records
            <div class="float-end">
                <a href="{{ route('super-admin.attendance.summary') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-chart-bar me-1"></i> Summary Report
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
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="section_id" class="form-select" id="sectionFilter">
                            <option value="">All Sections</option>
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
                        <a href="{{ route('super-admin.attendance.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Bulk Delete Form -->
            <form id="bulkDeleteForm" action="{{ route('super-admin.attendance.bulk-delete') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn" style="display: none;">
                        <i class="fas fa-trash me-1"></i> Delete Selected
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Marked By</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $attendance->id }}" class="record-checkbox"></td>
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
                                <td>{{ $attendance->markedByUser->name ?? 'System' }}</td>
                                <td>{{ $attendance->remarks ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('super-admin.attendance.edit', $attendance) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('super-admin.attendance.destroy', $attendance) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    }</div>
                </div>
            </form>
            
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Load sections based on selected class
    $('#classFilter').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '{{ route("sections.by-class", "") }}/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionFilter');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                    
                    // Also load students for this class
                    $.ajax({
                        url: '{{ route("students.by-class", "") }}/' + classId,
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
    
    // Select All functionality
    $('#selectAll').change(function() {
        $('.record-checkbox').prop('checked', $(this).prop('checked'));
        $('#bulkDeleteBtn').toggle($('.record-checkbox:checked').length > 0);
    });
    
    $('.record-checkbox').change(function() {
        $('#bulkDeleteBtn').toggle($('.record-checkbox:checked').length > 0);
    });
    
    $('#bulkDeleteBtn').click(function() {
        if ($('.record-checkbox:checked').length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to delete " + $('.record-checkbox:checked').length + " attendance records.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#bulkDeleteForm').submit();
                }
            });
        }
    });
</script>
@endpush