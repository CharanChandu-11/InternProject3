{{-- resources/views/parent/children/timetable.blade.php --}}
@extends('layouts.parent')

@section('title', 'Timetable - ' . $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-clock me-2"></i> Timetable: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Time Slot</th>
                            @foreach($days as $day)
                                <th>{{ ucfirst($day) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeSlots as $slot)
                            <tr>
                                <td class="bg-light">
                                    {{ $slot->time_range }}
                                    @if($slot->is_break)
                                        <span class="badge bg-warning ms-1">Break</span>
                                    @endif
                                 </span></td>
                                @foreach($days as $day)
                                    @php
                                        $class = $timetable[$day]->firstWhere('time_slot_id', $slot->id);
                                    @endphp
                                    <td class="{{ $class ? 'table-primary' : '' }}">
                                        @if($class)
                                            <strong>{{ $class->subject->name }}</strong><br>
                                            <small>{{ $class->teacher->name }}</small><br>
                                            <small>Room: {{ $class->room_number }}</small>
                                        @elseif($slot->is_break)
                                            <span class="text-muted">Break</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                     </span></td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection