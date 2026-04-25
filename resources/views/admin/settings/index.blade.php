{{-- resources/views/admin/settings/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-cog me-2"></i> System Settings
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="fas fa-building me-1"></i> General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">
                        <i class="fas fa-graduation-cap me-1"></i> Academic
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fees-tab" data-bs-toggle="tab" data-bs-target="#fees" type="button" role="tab">
                        <i class="fas fa-rupee-sign me-1"></i> Fees
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
                        <i class="fas fa-calendar-check me-1"></i> Attendance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" type="button" role="tab">
                        <i class="fas fa-bell me-1"></i> Notifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                        <i class="fas fa-database me-1"></i> Backup & Restore
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                        <i class="fas fa-server me-1"></i> System Info
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">
                <!-- General Settings Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    @include('admin.settings.partials.general')
                </div>

                <!-- Academic Settings Tab -->
                <div class="tab-pane fade" id="academic" role="tabpanel">
                    @include('admin.settings.partials.academic')
                </div>

                <!-- Fees Settings Tab -->
                <div class="tab-pane fade" id="fees" role="tabpanel">
                    @include('admin.settings.partials.fees')
                </div>

                <!-- Attendance Settings Tab -->
                <div class="tab-pane fade" id="attendance" role="tabpanel">
                    @include('admin.settings.partials.attendance')
                </div>

                <!-- Notification Settings Tab -->
                <div class="tab-pane fade" id="notification" role="tabpanel">
                    @include('admin.settings.partials.notification')
                </div>

                <!-- Backup & Restore Tab -->
                <div class="tab-pane fade" id="backup" role="tabpanel">
                    @include('admin.settings.partials.backup')
                </div>

                <!-- System Info Tab -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    @include('admin.settings.partials.system-info')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        padding: 0 20px;
        padding-top: 15px;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 500;
        padding: 12px 20px;
        margin-right: 5px;
        border-radius: 8px 8px 0 0;
        transition: all 0.3s;
    }
    .nav-tabs .nav-link:hover {
        color: #4361ee;
        background: rgba(67, 97, 238, 0.05);
    }
    .nav-tabs .nav-link.active {
        color: #4361ee;
        background: white;
        border-bottom: 2px solid #4361ee;
    }
    .nav-tabs .nav-link i {
        margin-right: 8px;
    }
    .tab-content {
        background: white;
    }
    .form-label {
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 8px;
        color: #334155;
    }
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
        transition: all 0.3s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
    .btn-primary {
        background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
        border: none;
        border-radius: 10px;
        padding: 10px 24px;
        font-weight: 500;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    .info-box {
        background: #f8fafc;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .info-box h6 {
        margin-bottom: 10px;
        font-weight: 600;
        color: #1e293b;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    }
    .table th {
        font-weight: 600;
        color: #475569;
    }
</style>
@endpush

@push('scripts')
<script>
    // Store active tab in localStorage
    $(document).ready(function() {
        var activeTab = localStorage.getItem('activeSettingsTab');
        if (activeTab) {
            $('#settingsTab button[data-bs-target="' + activeTab + '"]').tab('show');
        }
        
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            localStorage.setItem('activeSettingsTab', $(e.target).attr('data-bs-target'));
        });
    });
</script>
@endpush