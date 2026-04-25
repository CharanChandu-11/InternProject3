{{-- resources/views/admin/timetable/edit-grid.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Edit Timetable - ' . $class->name . ' - Section ' . $section->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> 
            Edit Timetable: {{ $class->name }} - Section {{ $section->name }}
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.timetable.update-grid', [$class, $section]) }}" method="POST">
                @csrf
                
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
                                        @if($slot->is_break)
                                            <span class="badge bg-warning ms-2">Break</span>
                                        @endif
                                    </td>
                                    @foreach($days as $day)
                                        <td style="min-width: 250px;">
                                            @php
                                                $entryKey = $day . '_' . $slot->id;
                                                $entry = $entries[$entryKey] ?? null;
                                            @endphp
                                            <input type="hidden" name="entries[{{ $loop->parent->index }}][{{ $loop->index }}][day]" value="{{ $day }}">
                                            <input type="hidden" name="entries[{{ $loop->parent->index }}][{{ $loop->index }}][time_slot_id]" value="{{ $slot->id }}">
                                            
                                            <div class="mb-2">
                                                <select name="entries[{{ $loop->parent->index }}][{{ $loop->index }}][subject_id]" class="form-select form-select-sm">
                                                    <option value="">-- Select Subject --</option>
                                                    @foreach($subjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ $entry && $entry->subject_id == $subject->id ? 'selected' : '' }}>
                                                            {{ $subject->name }} ({{ $subject->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <select name="entries[{{ $loop->parent->index }}][{{ $loop->index }}][teacher_id]" class="form-select form-select-sm">
                                                    <option value="">-- Select Teacher --</option>
                                                    @foreach($teachers as $teacher)
                                                        <option value="{{ $teacher->id }}" {{ $entry && $entry->teacher_id == $teacher->id ? 'selected' : '' }}>
                                                            {{ $teacher->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <input type="text" name="entries[{{ $loop->parent->index }}][{{ $loop->index }}][room_number]" 
                                                       class="form-control form-control-sm" placeholder="Room No." 
                                                       value="{{ $entry ? $entry->room_number : '' }}">
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save All Changes
                    </button>
                    <a href="{{ route('super-admin.timetable.index', ['class_id' => $class->id, 'section_id' => $section->id]) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    {{-- <a href="{{ route('super-admin.timetable.export', [$class, $section]) }}" class="btn btn-info float-end">
                        <i class="fas fa-download me-1"></i> Export Excel
                    </a> --}}
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Optional: auto-populate teacher based on subject selection (if needed)
    // You can implement AJAX to fetch assigned teacher for a subject/class
</script>
@endpush