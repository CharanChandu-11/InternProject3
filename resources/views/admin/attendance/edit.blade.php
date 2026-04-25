{{-- resources/views/admin/attendance/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Attendance')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Attendance Record
            <div class="float-end">
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Records
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Editing attendance for <strong>{{ $attendance->attendable->user->name }}</strong> 
                ({{ $attendance->attendable->admission_number }}) on 
                <strong>{{ $attendance->attendance_date->format('F j, Y') }}</strong>
            </div>

            <form action="{{ route('admin.attendance.update', $attendance) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="btn-group" role="group">
                            @foreach($statuses as $status)
                                <input type="radio" name="status" value="{{ $status }}" 
                                       id="status_{{ $status }}" 
                                       {{ $attendance->status == $status ? 'checked' : '' }}>
                                <label for="status_{{ $status }}" class="btn btn-outline-{{ 
                                    $status == 'present' ? 'success' : 
                                    ($status == 'absent' ? 'danger' : 
                                    ($status == 'late' ? 'warning' : 'info')) 
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </label>
                            @endforeach
                        </div>
                        @error('status')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" 
                               value="{{ $attendance->remarks }}" 
                               placeholder="Optional remarks">
                        @error('remarks')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Record
                    </button>
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

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

@endsection