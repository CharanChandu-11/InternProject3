{{-- resources/views/admin/parents/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Parents')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> Parent Management
            <div class="float-end">
                <a href="{{ route('admin.parents.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Parent
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="parent_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($parentTypes as $type)
                                <option value="{{ $type }}" {{ request('parent_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email, phone, occupation..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Children</th>
                            <th>Occupation</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parents as $parent)
                        <tr>
                            <td>
                                <img src="{{ $parent->profile_photo_url }}" alt="" 
                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td class="fw-bold">{{ $parent->name }}</td>
                            <td>{{ $parent->email }}</td>
                            <td>{{ $parent->phone }}</td>
                            <td>
                                <span class="badge bg-{{ $parent->parent->parent_type == 'father' ? 'info' : ($parent->parent->parent_type == 'mother' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($parent->parent->parent_type) }}
                                </span>
                            </td>
                            <td>
                                @if($parent->parent->children->count() > 0)
                                    <span class="badge bg-success">
                                        {{ $parent->parent->children->count() }} Child(ren)
                                    </span>
                                    <div class="small text-muted mt-1">
                                        @foreach($parent->parent->children->take(2) as $child)
                                            <div>{{ $child->user->name }} ({{ $child->class->name ?? 'N/A' }})</div>
                                        @endforeach
                                        @if($parent->parent->children->count() > 2)
                                            <div>+{{ $parent->parent->children->count() - 2 }} more</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">No children</span>
                                @endif
                            </td>
                            <td>{{ $parent->parent->occupation ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $parent->is_active ? 'success' : 'danger' }}">
                                    {{ $parent->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.parents.show', $parent) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.parents.toggle-status', $parent) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" title="{{ $parent->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $parent->is_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.parents.destroy', $parent) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Delete this parent? All associated data will be removed.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                No parents found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $parents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection