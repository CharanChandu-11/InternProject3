@extends('layouts.admin')
@section('title', 'Direct Room Allocation')
@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus me-2"></i> Directly Allocate Room to Student
    </div>
    <div class="card-body">
        <form action="{{ route('admin.hostel-allocations.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Student <span class="text-danger">*</span></label>
                <select name="student_id" class="form-select" required>
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }} ({{ $student->admission_number }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Room <span class="text-danger">*</span></label>
                <select name="hostel_room_id" class="form-select" required>
                    <option value="">Select Room</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ (old('hostel_room_id') == $room->id || ($selectedRoom && $selectedRoom->id == $room->id)) ? 'selected' : '' }}>
                            {{ $room->hostel->name }} - Room {{ $room->room_number }} ({{ ucfirst($room->room_type) }}, {{ $room->available_seats }} seats available)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class->mb-3">
                <label class="form-label">Allocation Date <span class="text-danger">*</span></label>
                <input type="date" name="allocation_date" class="form-control" value="{{ old('allocation_date', date('Y-m-d')) }}" required>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Allocate</button>
                <a href="{{ route('admin.hostel-allocations.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection