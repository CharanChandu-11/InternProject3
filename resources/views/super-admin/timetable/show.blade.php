{{-- resources/views/admin/timetable/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Timetable Entry Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Timetable Entry Details
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 150px;">Class</th>
                            <td>{{ $timetable->class->name }}</td>
                        </tr>
                        <tr>
                            <th>Section</th>
                            <td>{{ $timetable->section->name }}</td>
                        </tr>
                        <tr>
                            <th>Day</th>
                            <td>{{ ucfirst($timetable->day_of_week) }}</td>
                        </tr>
                        <tr>
                            <th>Time Slot</th>
                            <td>{{ \Carbon\Carbon::parse($timetable->timeSlot->start_time)->format('h:i A') }} - 
                                {{ \Carbon\Carbon::parse($timetable->timeSlot->end_time)->format('h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Subject</th>
                            <td>{{ $timetable->subject->name }} ({{ $timetable->subject->code }})</td>
                        </tr>
                        <tr>
                            <th>Teacher</th>
                            <td>{{ $timetable->teacher->name }}</td>
                        </tr>
                        <tr>
                            <th>Room Number</th>
                            <td>{{ $timetable->room_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $timetable->created_at->format('F j, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $timetable->updated_at->format('F j, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="{{ route('super-admin.timetable.edit', $timetable) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.timetable.index', ['class_id' => $timetable->class_id, 'section_id' => $timetable->section_id]) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Timetable
                </a>
                <form action="{{ route('super-admin.timetable.destroy', $timetable) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this timetable entry?')">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection