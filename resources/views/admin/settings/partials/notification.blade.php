{{-- resources/views/admin/settings/partials/notification.blade.php --}}
<form action="{{ route('admin.settings.notification') }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <h6><i class="fas fa-envelope me-2"></i> Email Notifications</h6>
            <hr>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="enable_email_notifications" class="form-check-input" value="1" 
                       {{ (old('enable_email_notifications', $notificationSettings['enable_email_notifications'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Enable Email Notifications</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="send_email_on_attendance" class="form-check-input" value="1" 
                       {{ (old('send_email_on_attendance', $notificationSettings['send_email_on_attendance'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Send email on attendance marking</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="send_email_on_fee_payment" class="form-check-input" value="1" 
                       {{ (old('send_email_on_fee_payment', $notificationSettings['send_email_on_fee_payment'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Send email on fee payment</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-4">
            <h6><i class="fas fa-sms me-2"></i> SMS Notifications</h6>
            <hr>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="enable_sms_notifications" class="form-check-input" value="1" 
                       {{ (old('enable_sms_notifications', $notificationSettings['enable_sms_notifications'] ?? false) ? 'checked' : '') }}>
                <label class="form-check-label">Enable SMS Notifications</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="send_sms_on_attendance" class="form-check-input" value="1" 
                       {{ (old('send_sms_on_attendance', $notificationSettings['send_sms_on_attendance'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Send SMS on absence/late arrival</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="send_sms_on_result" class="form-check-input" value="1" 
                       {{ (old('send_sms_on_result', $notificationSettings['send_sms_on_result'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Send SMS when results are published</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-4">
            <h6><i class="fas fa-bell me-2"></i> Push Notifications</h6>
            <hr>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="enable_push_notifications" class="form-check-input" value="1" 
                       {{ (old('enable_push_notifications', $notificationSettings['enable_push_notifications'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Enable Push Notifications</label>
            </div>
        </div>
        
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Notification Settings
            </button>
        </div>
    </div>
</form>