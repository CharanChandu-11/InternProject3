{{-- resources/views/admin/timetable/edit.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Edit Timetable Entry')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Timetable Entry
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.timetable.update', $timetable) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $timetable->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <select name="section_id" class="form-select @error('section_id') is-invalid @enderror" required>
                            <option value="">Select Section</option>
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Day <span class="text-danger">*</span></label>
                        <select name="day_of_week[]" class="form-select select2 @error('day_of_week') is-invalid @enderror" required multiple>
                            <option value="monday" {{ in_array('monday', old('day_of_week', [$timetable->day_of_week])) ? 'selected' : '' }}>Monday</option>
                            <option value="tuesday" {{ in_array('tuesday', old('day_of_week', [$timetable->day_of_week])) ? 'selected' : '' }}>Tuesday</option>
                            <option value="wednesday" {{ in_array('wednesday', old('day_of_week', [$timetable->day_of_week])) ? 'selected' : '' }}>Wednesday</option>
                            <option value="thursday" {{ in_array('thursday', old('day_of_week', [$timetable->day_of_week])) ? 'selected' : '' }}>Thursday</option>
                            <option value="friday" {{ in_array('friday', old('day_of_week', [$timetable->day_of_week])) ? 'selected' : '' }}>Friday</option>
                            <option value="saturday" {{ in_array('saturday', old('day_of_week', [$timetable->day_of_week])) ? 'selected' : '' }}>Saturday</option>
                        </select>
                        @error('day_of_week')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Time Slot <span class="text-danger">*</span></label>
                        <select name="time_slot_id" class="form-select @error('time_slot_id') is-invalid @enderror" required>
                            <option value="">Select Time Slot</option>
                            @foreach($timeSlots as $slot)
                                <option value="{{ $slot->id }}" {{ old('time_slot_id', $timetable->time_slot_id) == $slot->id ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                                    @if($slot->is_break) (Break) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('time_slot_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $timetable->subject_id) == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teacher <span class="text-danger">*</span></label>
                        <select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $timetable->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->employee?->designation ?? 'Teacher' }})
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" 
                               value="{{ old('room_number', $timetable->room_number) }}" placeholder="e.g., Room 101">
                        @error('room_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Entry
                    </button>
                    <a href="{{ route('super-admin.timetable.index', ['class_id' => $timetable->class_id, 'section_id' => $timetable->section_id]) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
    function loadSections(classId, selectedSection = null) {
        if (classId) {
            $.ajax({
                url: '{{ url("admin/sections/by-class") }}/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('select[name="section_id"]');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">Select Section</option>');
                    $.each(data, function(key, section) {
                        var selected = (selectedSection == section.id) ? 'selected' : '';
                        sectionSelect.append('<option value="' + section.id + '" ' + selected + '>' + section.name + '</option>');
                    });
                },
                error: function(xhr) {
                    console.log('Error loading sections:', xhr);
                }
            });
        } else {
            $('select[name="section_id"]').empty().append('<option value="">Select Section</option>');
        }
    }
    
    var initialClass = $('select[name="class_id"]').val();
    var initialSection = '{{ old("section_id", $timetable->section_id ?? '') }}';
    if (initialClass) {
        loadSections(initialClass, initialSection);
    }
    
    $('select[name="class_id"]').change(function() {
        loadSections($(this).val());
    });
});
</script>
@endpush