{{-- resources/views/admin/settings/partials/backup-list.blade.php --}}
@if(isset($backups) && count($backups) > 0)
    @foreach($backups as $backup)
    <tr>
        <td>{{ $backup['name'] }}</td>
        <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
        <td>{{ $backup['date'] }}</td>
        <td>
            <a href="{{ Storage::url('backups/' . $backup['name']) }}" class="btn btn-sm btn-info" download>
                <i class="fas fa-download"></i>
            </a>
            <button type="button" class="btn btn-sm btn-danger delete-backup" data-filename="{{ $backup['name'] }}">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
    @endforeach
@else
    <tr>
        <td colspan="4" class="text-center text-muted">No backups found</td>
    </tr>
@endif

@push('scripts')
<script>
    $(document).on('click', '.delete-backup', function() {
        var filename = $(this).data('filename');
        Swal.fire({
            title: 'Delete Backup?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.settings.backup.delete", "") }}/' + filename,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        location.reload();
                    }
                });
            }
        });
    });
</script>
@endpush