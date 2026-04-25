{{-- resources/views/admin/settings/system-info.blade.php --}}
@extends('layouts.admin')

@section('title', 'System Information')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-info-circle me-2"></i> System Information
            <div class="float-end">
                <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Settings
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Laravel Version</th>
                            <td>{{ $info['laravel_version'] }}</td>
                        </tr>
                        <tr>
                            <th>PHP Version</th>
                            <td>{{ $info['php_version'] }}</td>
                        </tr>
                        <tr>
                            <th>Database Version</th>
                            <td>{{ $info['mysql_version'] }}</td>
                        </tr>
                        <tr>
                            <th>Server Software</th>
                            <td>{{ $info['server_software'] }}</td>
                        </tr>
                        <tr>
                            <th>Server Time</th>
                            <td>{{ $info['server_time'] }}</td>
                        </tr>
                        <tr>
                            <th>Timezone</th>
                            <td>{{ $info['timezone'] }}</td>
                        </tr>
                        <tr>
                            <th>Debug Mode</th>
                            <td>
                                <span class="badge bg-{{ $info['debug_mode'] == 'Enabled' ? 'danger' : 'success' }}">
                                    {{ $info['debug_mode'] }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Environment</th>
                            <td>{{ $info['environment'] }}</td>
                        </tr>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection