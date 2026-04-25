{{-- resources/views/admin/timetable/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Timetable Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Timetable Management
            <div class="float-end">
                <a href="{{ route('super-admin.timetable.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Single Entry
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="class_id" class="form-select" id="classSelect">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="section_id" class="form-select" id="sectionSelect">
                            <option value="">Select Section</option>
                            @if($selectedClass)
                                @foreach($selectedClass->sections as $section)
                                    <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                        {{ $section->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">View</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('super-admin.timetable.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            @if($selectedClass && $selectedSection)
                <div class="mb-3">
                    {{-- <a href="{{ route('super-admin.timetable.edit-grid', [$selectedClass, $selectedSection]) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit Grid (All Days)
                    </a> --}}
                    {{-- <a href="{{ route('super-admin.timetable.export', [$selectedClass, $selectedSection]) }}" class="btn btn-info">
                        <i class="fas fa-download me-1"></i> Export Excel
                    </a> --}}
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Time Slot</th>
                                @foreach($days as $day)
                                    <th class="text-center">{{ ucfirst($day) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slot)
                                <tr>
                                    <td class="bg-light">
                                        <strong>{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - 
                                               {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</strong>
                                    </td>
                                    @foreach($days as $day)

                                       @php
                                            $currentDay = now()->format('l');
                                            $currentTime = now()->format('H:i:s');

                                            $isToday = strtolower($day) === strtolower($currentDay);

                                            $isCurrentSlot = (
                                                $currentTime >= Carbon\Carbon::parse($slot->start_time)->format('H:i:s') &&
                                                $currentTime <= Carbon\Carbon::parse($slot->end_time)->format('H:i:s')
                                            );
                                        @endphp

                                        <td class="align-middle {{ $isToday ? 'today-col' : '' }} {{ ($isToday && $isCurrentSlot && !$slot->is_break) ? 'current-slot' : '' }}">
                                            @php $entry = $timetable[$day][$slot->id] ?? null; @endphp
                                            @if($entry && !$slot->is_break)
                                                <div><strong>{{ $entry->subject->name }}</strong></div>
                                                <div><small>{{ $entry->teacher->name }}</small></div>
                                                @if(!empty($entry->room_number))
                                                    <div><small>Room: {{ $entry->room_number }}</small></div>
                                                @endif
                                                
                                                <div class="mt-1">
                                                    <a href="{{ route('super-admin.timetable.edit', $entry) }}" class="btn btn-xs btn-outline-primary px-2 py-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('super-admin.timetable.destroy', $entry) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="button" class="btn btn-xs btn-outline-danger delete-btn px-2 py-1">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif($slot->is_break)
                                                <span class="text-muted">Break</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Select a class and section to view timetable.</div>
            @endif
        </div>
    </div>
</div>
@endsection
@push('styles')
<style id="w8o3gq">
/* 🔵 Current Day Column */
.today-col {
    background: linear-gradient(180deg, #e3f2fd, #ffffff);
}

/* 🟢 Current Time Slot (highlight) */
.current-slot {
    background: linear-gradient(135deg, #00c853, #69f0ae) !important;
    color: #000;
    font-weight: 600;
    border: 2px solid #00a152;
    box-shadow: 0 0 10px rgba(0, 200, 83, 0.4);
}

/* ✨ Hover Effect */
.table td:hover {
    background: #f5f5f5;
    transition: 0.3s;
}

/* 📦 Card polish */
.card {
    border-radius: 12px;
    overflow: hidden;
}

/* 🧊 Table UI */
.table th {
    background: #f8f9fc;
    text-transform: uppercase;
    font-size: 13px;
}

.table td {
    vertical-align: middle;
}

/* 🔘 Buttons */
.btn-xs {
    font-size: 12px;
    border-radius: 6px;
}
</style>
@endpush
@push('scripts')
<script>
    $('#classSelect').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/admin/get-sections/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionSelect');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">Select Section</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionSelect').empty().append('<option value="">Select Section</option>');
        }
    });
</script>
@endpush