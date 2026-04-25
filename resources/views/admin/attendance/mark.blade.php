{{-- resources/views/admin/attendance/mark.blade.php --}}
@extends('layouts.admin')

@section('title', 'Mark Attendance')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-check-circle me-2"></i> Mark Attendance
            <div class="float-end">
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Records
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Marking attendance for <strong>{{ $class->name }} - Section {{ $section->name }}</strong> on 
                <strong>{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</strong>
            </div>

            <form action="{{ route('admin.attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $class->id }}">
                <input type="hidden" name="section_id" value="{{ $section->id }}">
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Admission No</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php
                                    $existing = $existingAttendance[$student->id] ?? null;
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $student->roll_number }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $student->user->profile_photo_url }}" 
                                                 alt="" class="rounded-circle me-2" 
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                            {{ $student->user->name }}
                                        </div>
                                    </td>
                                    <td>{{ $student->admission_number }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <input type="radio" name="attendance[{{ $student->id }}][student_id]" value="{{ $student->id }}" hidden>
                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="present" 
                                                   id="present_{{ $student->id }}" 
                                                   {{ $existing && $existing->status == 'present' ? 'checked' : '' }}>
                                            <label for="present_{{ $student->id }}" class="btn btn-sm btn-outline-success">Present</label>
                                            
                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="absent" 
                                                   id="absent_{{ $student->id }}" 
                                                   {{ $existing && $existing->status == 'absent' ? 'checked' : '' }}>
                                            <label for="absent_{{ $student->id }}" class="btn btn-sm btn-outline-danger">Absent</label>
                                            
                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="late" 
                                                   id="late_{{ $student->id }}" 
                                                   {{ $existing && $existing->status == 'late' ? 'checked' : '' }}>
                                            <label for="late_{{ $student->id }}" class="btn btn-sm btn-outline-warning">Late</label>
                                            
                                            <input type="radio" name="attendance[{{ $student->id }}][status]" value="half_day" 
                                                   id="half_day_{{ $student->id }}" 
                                                   {{ $existing && $existing->status == 'half_day' ? 'checked' : '' }}>
                                            <label for="half_day_{{ $student->id }}" class="btn btn-sm btn-outline-info">Half Day</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="attendance[{{ $student->id }}][remarks]" 
                                               class="form-control form-control-sm" 
                                               placeholder="Remarks (optional)"
                                               value="{{ $existing?->remarks ?? '' }}">
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
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    .btn-group input[type="radio"] {
        display: none;
    }
    .btn-group .btn {
        margin: 0;
    }
    .btn-group input[type="radio"]:checked + label {
        background-color: #007bff;
        color: white;
    }
    .btn-group input[type="radio"]:checked + label.btn-outline-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    .btn-group input[type="radio"]:checked + label.btn-outline-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .btn-group input[type="radio"]:checked + label.btn-outline-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    .btn-group input[type="radio"]:checked + label.btn-outline-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
</style>
@endpush