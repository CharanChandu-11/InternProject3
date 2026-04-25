{{-- resources/views/admin/settings/partials/backup.blade.php --}}
<div class="row">
    <div class="col-md-6">
        <div class="card bg-light mb-4">
            <div class="card-body text-center">
                <i class="fas fa-database fa-4x text-primary mb-3 d-block"></i>
                <h5>Database Backup</h5>
                <p class="text-muted">Create a complete backup of your database</p>
                <form action="{{ route('admin.settings.backup') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Download Backup
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card bg-light mb-4">
            <div class="card-body text-center">
                <i class="fas fa-upload fa-4x text-success mb-3 d-block"></i>
                <h5>Restore Database</h5>
                <p class="text-muted">Restore from a backup file</p>
                <form action="{{ route('admin.settings.restore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="backup_file" class="form-control mb-2" accept=".sql,.zip" required>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-1"></i> Restore Backup
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 mt-3">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Backup History
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="backupList">
                            @php
                                $backupPath = storage_path('app/backups');
                                $backups = [];
                                if (file_exists($backupPath)) {
                                    $backups = array_reverse(glob($backupPath . '/*.sql'));
                                }
                            @endphp
                            @forelse($backups as $backup)
                                @php
                                    $filename = basename($backup);
                                    $size = filesize($backup);
                                    $date = date('Y-m-d H:i:s', filemtime($backup));
                                @endphp
                                <tr>
                                    <td>{{ $filename }}</td>
                                    <td>{{ number_format($size / 1024, 2) }} KB</td>
                                    <td>{{ $date }}</td>
                                    <td>
                                        <a href="{{ Storage::url('backups/' . $filename) }}" class="btn btn-sm btn-info" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No backups found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh backup list after backup
    function refreshBackupList() {
        $.ajax({
            url: '{{ route("admin.settings.backup-list") }}',
            type: 'GET',
            success: function(data) {
                $('#backupList').html(data);
            }
        });
    }
</script>
@endpush