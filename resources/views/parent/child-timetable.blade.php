{{-- resources/views/parent/child-timetable.blade.php --}}
@extends('layouts.parent')

@section('title', $student->user->name . ' - Timetable')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-clock me-2"></i> 
                            Weekly Timetable - {{ $student->user->name }}
                        </div>
                        <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered timetable-table">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center">Time</th>
                                    @foreach($days as $day)
                                        <th class="text-center">{{ ucfirst($day) }}</th>
                                    @endforeach
                                 </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $slot)
                                     <tr>
                                        <td class="fw-bold text-center bg-light">
                                            {{ $slot->time_range }}
                                            @if($slot->is_break)
                                                <i class="fas fa-coffee ms-1 text-muted"></i>
                                            @endif
                                         </td>
                                        @foreach($days as $day)
                                            @php $class = $formattedTimetable[$day][$slot->id] ?? null; @endphp
                                            @if($class)
                                                <td class="text-center">
                                                    <div class="fw-bold">{{ $class->subject->name }}</div>
                                                    <small class="text-muted">{{ $class->teacher->name }}</small>
                                                    @if($class->room_number)
                                                        <div class="small text-primary">Room: {{ $class->room_number }}</div>
                                                    @endif
                                                 </td>
                                            @elseif($slot->is_break)
                                                <td class="bg-light text-center text-muted">
                                                    <i class="fas fa-mug-hot"></i><br>
                                                    <small>Break</small>
                                                 </td>
                                            @else
                                                <td class="text-center text-muted">—</td>
                                            @endif
                                        @endforeach
                                     </tr>
                                @endforeach
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timetable-table td, .timetable-table th {
        vertical-align: middle;
        padding: 15px 10px;
    }
    .timetable-table tr:hover td {
        background-color: #f8f9fa;
    }
</style>
@endpush