{{-- resources/views/admin/settings/partials/system-info.blade.php --}}
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-server me-2"></i> Server Information
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">PHP Version</th>
                        <td>{{ phpversion() }}</span></td>
                    </tr>
                    <tr>
                        <th>Laravel Version</th>
                        <td>{{ app()->version() }}</span></td>
                    </tr>
                    <tr>
                        <th>Server Software</th>
                        <td>{{ $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' }}</span></td>
                    </tr>
                    <tr>
                        <th>Server Protocol</th>
                        <td>{{ $_SERVER['SERVER_PROTOCOL'] ?? 'N/A' }}</span></td>
                    </tr>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-database me-2"></i> Database Information
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Database Type</th>
                        <td>{{ DB::connection()->getDriverName() }}</span></td>
                    </tr>
                    @php
                        $databaseName = DB::connection()->getDatabaseName();
                        $databaseSize = 0;
                        if (file_exists($databaseName)) {
                            $databaseSize = filesize($databaseName);
                        }
                    @endphp
                    <tr>
                        <th>Database Name</th>
                        <td>{{ basename($databaseName) }}</span></td>
                    </tr>
                    <tr>
                        <th>Database Size</th>
                        <td>{{ $databaseSize > 0 ? number_format($databaseSize / 1024, 2) . ' KB' : 'N/A' }}</span></td>
                    </tr>
                    <tr>
                        <th>Total Tables</th>
                        <td>{{ count(DB::select('SHOW TABLES')) }}</span></td>
                    </tr>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="fas fa-chart-line me-2"></i> Application Statistics
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Total Users</th>
                        <td>{{ number_format(\App\Models\User::count()) }}</span></td>
                    </tr>
                    <tr>
                        <th>Total Students</th>
                        <td>{{ number_format(\App\Models\Student::count()) }}</span></td>
                    </tr>
                    <tr>
                        <th>Total Teachers</th>
                        <td>{{ number_format(\App\Models\User::where('user_type', 'teacher')->count()) }}</span></td>
                    </tr>
                    <tr>
                        <th>Total Parents</th>
                        <td>{{ number_format(\App\Models\User::where('user_type', 'parent')->count()) }}</span></td>
                    </tr>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-cog me-2"></i> System Configuration
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Environment</th>
                        <td>{{ app()->environment() }}</span></td>
                    </tr>
                    <tr>
                        <th>Debug Mode</th>
                        <td>{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</span></td>
                    </tr>
                    <tr>
                        <th>Cache Driver</th>
                        <td>{{ config('cache.default') }}</span></td>
                    </tr>
                    <tr>
                        <th>Session Driver</th>
                        <td>{{ config('session.driver') }}</span></td>
                    </tr>
                    <tr>
                        <th>Queue Driver</th>
                        <td>{{ config('queue.default') }}</span></td>
                    </tr>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-microchip me-2"></i> PHP Extensions
            </div>
            <div class="card-body">
                @php
                    $requiredExtensions = ['bcmath', 'ctype', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'curl', 'gd', 'zip'];
                    $loadedExtensions = get_loaded_extensions();
                @endphp
                <div class="row">
                    @foreach($requiredExtensions as $ext)
                        <div class="col-md-3 mb-2">
                            <i class="fas fa-{{ in_array($ext, $loadedExtensions) ? 'check-circle text-success' : 'times-circle text-danger' }} me-2"></i>
                            {{ $ext }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 mt-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-trash-alt me-2"></i> Cache Management
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-broom me-1"></i> Clear All Cache
                    </button>
                </form>
                
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="type" value="config">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-1"></i> Clear Config
                    </button>
                </form>
                
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="type" value="route">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-route me-1"></i> Clear Routes
                    </button>
                </form>
                
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="type" value="view">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i> Clear Views
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>