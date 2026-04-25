{{-- resources/views/admin/hostels/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Hostels')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-hotel me-2"></i> Hostel Management
            <div class="float-end">
                <a href="{{ route('admin.hostels.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Hostel
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="boys" {{ request('type') == 'boys' ? 'selected' : '' }}>Boys</option>
                            <option value="girls" {{ request('type') == 'girls' ? 'selected' : '' }}>Girls</option>
                            <option value="co_ed" {{ request('type') == 'co_ed' ? 'selected' : '' }}>Co-Ed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or warden..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.hostels.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Hostel Name</th>
                            <th>Type</th>
                            <th>Warden Name</th>
                            <th>Warden Phone</th>
                            <th>Total Rooms</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hostels as $hostel)
                        <tr>
                            <td class="fw-bold">{{ $hostel->name }}</td>
                            <td>
                                <span class="badge bg-{{ $hostel->type == 'boys' ? 'primary' : ($hostel->type == 'girls' ? 'danger' : 'info') }}">
                                    {{ ucfirst(str_replace('_', ' ', $hostel->type)) }}
                                </span>
                            </td>
                            <td>{{ $hostel->warden_name }}</td>
                            <td>{{ $hostel->warden_phone }}</td>
                            <td>{{ $hostel->rooms_count }}</td>
                            <td>{{ Str::limit($hostel->address, 50) }}</td>
                            <td>
                                <a href="{{ route('admin.hostels.show', $hostel) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.hostels.edit', $hostel) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.hostels.destroy', $hostel) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this hostel? This will also delete all rooms under it.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hostels found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $hostels->links() }}
        </div>
    </div>
</div>
@endsection