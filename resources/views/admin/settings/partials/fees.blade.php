{{-- resources/views/admin/settings/partials/fees.blade.php --}}
<form action="{{ route('admin.settings.fees') }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Late Fee Per Day (₹)</label>
            <input type="number" name="late_fee_per_day" class="form-control" 
                   value="{{ old('late_fee_per_day', $feeSettings['late_fee_per_day'] ?? 10) }}" min="0" step="0.01">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Grace Period (Days)</label>
            <input type="number" name="grace_period_days" class="form-control" 
                   value="{{ old('grace_period_days', $feeSettings['grace_period_days'] ?? 7) }}" min="0">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Payment Reminder Days</label>
            <input type="number" name="payment_reminder_days" class="form-control" 
                   value="{{ old('payment_reminder_days', $feeSettings['payment_reminder_days'] ?? 5) }}" min="1">
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Default Payment Method</label>
            <select name="default_payment_method" class="form-select">
                <option value="cash" {{ (old('default_payment_method', $feeSettings['default_payment_method'] ?? 'cash') == 'cash') ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ (old('default_payment_method', $feeSettings['default_payment_method'] ?? 'cash') == 'card') ? 'selected' : '' }}>Card</option>
                <option value="bank_transfer" {{ (old('default_payment_method', $feeSettings['default_payment_method'] ?? 'cash') == 'bank_transfer') ? 'selected' : '' }}>Bank Transfer</option>
                <option value="online" {{ (old('default_payment_method', $feeSettings['default_payment_method'] ?? 'cash') == 'online') ? 'selected' : '' }}>Online</option>
            </select>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="auto_generate_invoice" class="form-check-input" value="1" 
                       {{ (old('auto_generate_invoice', $feeSettings['auto_generate_invoice'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Auto-generate invoices for students</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="send_payment_reminders" class="form-check-input" value="1" 
                       {{ (old('send_payment_reminders', $feeSettings['send_payment_reminders'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Send payment reminders to parents</label>
            </div>
        </div>
        
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input type="checkbox" name="enable_online_payment" class="form-check-input" value="1" 
                       {{ (old('enable_online_payment', $feeSettings['enable_online_payment'] ?? true) ? 'checked' : '') }}>
                <label class="form-check-label">Enable online payment gateway</label>
            </div>
        </div>
        
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Fees Settings
            </button>
        </div>
    </div>
</form>