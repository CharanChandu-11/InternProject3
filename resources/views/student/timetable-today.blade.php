{{-- resources/views/student/timetable-today.blade.php --}}
@extends('layouts.student')

@section('title', 'Today\'s Timetable')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i> Today's Schedule - {{ now()->format('l, F j, Y') }}
                <a href="{{ route('student.timetable') }}" class="btn btn-sm btn-secondary float-end">Weekly View</a>
            </div>
            <div class="card-body">
                @if($currentClass)
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-chalkboard-user me-2"></i>
                        <strong>Current Class:</strong> {{ $currentClass->subject->name }} with {{ $currentClass->teacher->name }}
                        ({{ $currentClass->timeSlot->time_range }}) - Room {{ $currentClass->room_number ?? 'N/A' }}
                    </div>
                @endif
                
                @if($nextClass)
                    <div class="alert alert-warning mb-4">
                        <i class="fas fa-bell me-2"></i>
                        <strong>Next Class:</strong> {{ $nextClass->subject->name }} with {{ $nextClass->teacher->name }}
                        at {{ $nextClass->timeSlot->start_time->format('h:i A') }} - Room {{ $nextClass->room_number ?? 'N/A' }}
                    </div>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                             <tr>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Room</th>
                                <th>Status</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($timetable as $class)
                                 <tr class="{{ $currentClass && $currentClass->id == $class->id ? 'table-primary' : '' }}">
                                    <td class="fw-bold">{{ $class->timeSlot->time_range }}</td>
                                    <td>{{ $class->subject->name }}</td>
                                    <td>{{ $class->teacher->name }}</td>
                                    <td>{{ $class->room_number ?? '--' }}</td>
                                    <td>
                                        @if($currentClass && $currentClass->id == $class->id)
                                            <span class="badge bg-primary">Ongoing</span>
                                        @elseif($class->timeSlot->end_time < now()->format('H:i:s'))
                                            <span class="badge bg-secondary">Completed</span>
                                        @else
                                            <span class="badge bg-info">Upcoming</span>
                                        @endif
                                    </td>
                                 </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($timetable->isEmpty())
                    <p class="text-muted text-center py-4">No classes scheduled for today.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection