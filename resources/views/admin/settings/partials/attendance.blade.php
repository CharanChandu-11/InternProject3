{{-- resources/views/admin/settings/partials/attendance.blade.php --}}
<form action="{{ route('admin.settings.attendance') }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">School Start Time</label>
            <input type="time" name="school_start_time" class="form-control" 
                   value="{{ old('school_start_time', $attendanceSettings['school_start_time'] ?? '08:00') }}">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">School End Time</label>
            <input type="time" name="school_end_time" class="form-control" 
                   value="{{ old('school_end_time', $attendanceSettings['school_end_time'] ?? '15:00') }}">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Late Marking Threshold (Minutes)</label>
            <input type="number" name="late_threshold_minutes" class="form-control" 
                   value="{{ old('late_threshold_minutes', $attendanceSettings['late_threshold_minutes'] ?? 15) }}" min="0">
            <small class="text-muted">Student marked late after this many minutes</small>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Minimum Attendance Percentage Required</label>
            <input type="number" name="min_attendance_percentage" class="form-control" 
                   value="{{ old('min_attendance_percentage', $attendanceSettings['min_attendance_percentage'] ?? 75) }}" min="0" max="100">
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="enable_biometric" class="form-check-input" value="1" 
                       {{ (old('enable_biometric', $attendanceSettings['enable_biometric'] ?? false) ? 'checked' : '') }}>
                <label class="form-check-label">Enable Biometric Attendance</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="auto_mark_absent" class="form-check-input" value="1" 
                       {{ (old('auto_mark_absent', $attendanceSettings['auto_mark_absent'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Auto-mark absent after end time</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="send_attendance_alert" class="form-check-input" value="1" 
                       {{ (old('send_attendance_alert', $attendanceSettings['send_attendance_alert'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Send attendance alerts to parents</label>
            </div>
        </div>
        
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Attendance Settings
            </button>
        </div>
    </div>
</form>