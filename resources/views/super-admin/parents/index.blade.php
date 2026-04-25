{{-- resources/views/super-admin/parents/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Parent Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> Parent Management
            <div class="float-end">
                <a href="{{ route('super-admin.parents.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Parent
                </a>
                <a href="{{ route('super-admin.parents.import-form') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-upload me-1"></i> Import
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="parent_type" class="form-select">
                            <option value="">All Parent Types</option>
                            @foreach($parentTypes as $type)
                                <option value="{{ $type }}" {{ request('parent_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.parents.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Parents Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Parent Type</th>
                            <th>Occupation</th>
                            <th>Children</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parents as $parent)
                        <tr>
                            <td>
                                <img src="{{ $parent->profile_photo_url }}" alt="{{ $parent->name }}" 
                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $parent->name }}<br>
                                <small class="text-muted">{{ $parent->username }}</small>
                            </td>
                            <td>{{ $parent->email }}</td>
                            <td>{{ $parent->phone }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst(str_replace('_', ' ', $parent->parent->parent_type ?? 'N/A')) }}
                                </span>
                            </td>
                            <td>{{ $parent->parent->occupation ?? 'N/A' }}</td>
                            <td>
                                @if($parent->parent && $parent->parent->children->count() > 0)
                                    <span class="badge bg-primary">{{ $parent->parent->children->count() }} child(s)</span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $parent->is_active ? 'success' : 'danger' }}">
                                    {{ $parent->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.parents.show', $parent) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.parents.edit', $parent) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('super-admin.parents.toggle-status', $parent) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $parent->is_active ? 'warning' : 'success' }}">
                                        <i class="fas fa-{{ $parent->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('super-admin.parents.destroy', $parent) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $parents->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            pageLength: 10,
            responsive: true,
            searching: false,
            paging: false,
            info: false
        });
    });
</script>
@endpush