{{-- resources/views/teacher/attendance/mark.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Mark Attendance - ' . $class->name . ' - Section ' . $section->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-check me-2"></i> 
            Mark Attendance: {{ $class->name }} - Section {{ $section->name }}
        </div>
        <div class="card-body">
            <!-- Date Selection -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Select Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" 
                               max="{{ Carbon\Carbon::today()->format('Y-m-d') }}" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
            
            @if($alreadyMarked)
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i> 
                    Attendance has already been marked for {{ Carbon\Carbon::parse($date)->format('l, F j, Y') }}.
                    You can edit individual records below.
                </div>
            @endif
            
            <form action="{{ route('teacher.attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $class->id }}">
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                <input type="hidden" name="date" value="{{ $date }}">
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Photo</th>
                                <th>Student Name</th>
                                <th>Admission No</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            <tr>
                                <td class="text-center">{{ $student->roll_number ?? '-' }}</td>
                                <td class="text-center">
                                    <img src="{{ $student->user->profile_photo_url }}" alt="" 
                                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td>
                                    {{ $student->user->name }}<br>
                                    <small class="text-muted">{{ $student->user->email }}</small>
                                </td>
                                <td>{{ $student->admission_number }}</td>
                                <td>
                                    @php
                                        $existing = $existingAttendance[$student->id] ?? null;
                                        $currentStatus = $existing ? $existing->status : 'present';
                                    @endphp
                                    <input type="hidden" name="attendance[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm {{ $currentStatus == 'present' ? 'btn-success active' : 'btn-outline-success' }}" 
                                                onclick="setStatus(this, 'present', {{ $index }})">
                                            <i class="fas fa-check-circle"></i> Present
                                        </button>
                                        <button type="button" class="btn btn-sm {{ $currentStatus == 'absent' ? 'btn-danger active' : 'btn-outline-danger' }}" 
                                                onclick="setStatus(this, 'absent', {{ $index }})">
                                            <i class="fas fa-times-circle"></i> Absent
                                        </button>
                                        <button type="button" class="btn btn-sm {{ $currentStatus == 'late' ? 'btn-warning active' : 'btn-outline-warning' }}" 
                                                onclick="setStatus(this, 'late', {{ $index }})">
                                            <i class="fas fa-clock"></i> Late
                                        </button>
                                        <button type="button" class="btn btn-sm {{ $currentStatus == 'half_day' ? 'btn-info active' : 'btn-outline-info' }}" 
                                                onclick="setStatus(this, 'half_day', {{ $index }})">
                                            <i class="fas fa-sun"></i> Half Day
                                        </button>
                                    </div>
                                    <input type="hidden" name="attendance[{{ $index }}][status]" value="{{ $currentStatus }}" class="status-input-{{ $index }}">
                                </td>
                                <td>
                                    <input type="text" name="attendance[{{ $index }}][remarks]" class="form-control form-control-sm" 
                                           placeholder="Remarks" value="{{ $existing ? $existing->remarks : '' }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Attendance
                    </button>
                    <a href="{{ route('teacher.attendance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setStatus(button, status, index) {
        var container = button.closest('.btn-group');
        container.querySelectorAll('.btn').forEach(btn => {
            btn.classList.remove('active', 'btn-success', 'btn-danger', 'btn-warning', 'btn-info');
            btn.classList.add('btn-outline-success', 'btn-outline-danger', 'btn-outline-warning', 'btn-outline-info');
        });
        button.classList.add('active');
        if (status === 'present') button.classList.add('btn-success');
        if (status === 'absent') button.classList.add('btn-danger');
        if (status === 'late') button.classList.add('btn-warning');
        if (status === 'half_day') button.classList.add('btn-info');
        
        document.querySelector(`.status-input-${index}`).value = status;
    }
</script>
@endpush