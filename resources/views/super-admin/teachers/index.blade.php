{{-- resources/views/super-admin/teachers/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Teacher Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chalkboard-user me-2"></i> Teacher Management
            <div class="float-end">
                <a href="{{ route('super-admin.teachers.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Teacher
                </a>
                <a href="{{ route('super-admin.teachers.import-form') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-upload me-1"></i> Import
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
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
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, employee ID..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.teachers.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Teachers Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                        <tr>
                            <td><strong>{{ $teacher->employee->employee_id ?? 'N/A' }}</strong></td>
                            <td>
                                <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}" 
                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $teacher->name }}<br>
                                <small class="text-muted">Since: {{ $teacher->employee->joining_date->format('M Y') ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $teacher->email }}</td>
                            <td>{{ $teacher->employee->department ?? 'N/A' }}</td>
                            <td>{{ $teacher->employee->designation ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $teacher->is_active ? 'success' : 'danger' }}">
                                    {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.teachers.show', $teacher) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('super-admin.teachers.toggle-status', $teacher) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $teacher->is_active ? 'warning' : 'success' }}">
                                        <i class="fas fa-{{ $teacher->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('super-admin.teachers.destroy', $teacher) }}" method="POST" class="d-inline delete-form">
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
            
            {{ $teachers->links() }}
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