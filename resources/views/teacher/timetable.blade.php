{{-- resources/views/teacher/timetable.blade.php --}}
@extends('layouts.teacher')

@section('title', 'My Timetable')

@section('content')
@php
    $today = strtolower(\Carbon\Carbon::now()->format('l'));
    $currentTime = \Carbon\Carbon::now();
@endphp
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock me-2"></i> Weekly Schedule
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered timetable-table">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center">Time</th>
                                    @foreach($days as $day)
                                        <th class="text-center" {{ $day == $today ? 'today-col' : '' }}>{{ ucfirst($day) }}</th>
                                    @endforeach
                                 </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $slot)
                                    @php
                                        [$start, $end] = explode('-', $slot->time_range);

                                        try {
                                            $startTime = \Carbon\Carbon::parse(trim($start));
                                            $endTime = \Carbon\Carbon::parse(trim($end));
                                        } catch (\Exception $e) {
                                            $startTime = null;
                                            $endTime = null;
                                        }

                                        $isCurrentSlot = $startTime && $endTime 
                                            ? now()->between($startTime, $endTime)
                                            : false;
                                    @endphp

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
                                                <td class="text-center {{ $day == $today ? 'today-col' : '' }} {{ ($day == $today && $isCurrentSlot) ? 'current-cell' : '' }}">
                                                    <div class="fw-bold">{{ $class->subject->name }}</div>
                                                    <small class="text-muted">{{ $class->class->name }} - {{ $class->section->name }}</small>
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
    /* Today column */
    .today-col {
        background-color: #e3f2fd !important;
    }

    /* Current time row */
    .current-row td {
        background-color: #fff3cd !important;
    }

    /* Exact current cell (today + current time) */
    .current-cell {
        background-color: #17eb17 !important;
        font-weight: bold;
        border: 2px solid #034103;
    }
</style>
@endpush