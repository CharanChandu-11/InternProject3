{{-- resources/views/admin/teachers/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Teachers')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chalkboard-user me-2"></i> Teacher Management
            <div class="float-end">
                <a href="{{ route('admin.teachers.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Teacher
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="department" class="form-select" id="departmentFilter">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, employee ID..." value="{{ request('search') }}" id="searchInput">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered" id="teachersTable">
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
                        @forelse($teachers as $teacher)
                        <tr>
                            <td class="fw-bold">{{ $teacher->employee?->employee_id ?? 'N/A' }}</td>
                            <td>
                                <img src="{{ $teacher->profile_photo_url }}" alt="" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                            </td>
                            <td>{{ $teacher->name }}</td>
                            <td>{{ $teacher->email }}</td>
                            <td>{{ $teacher->employee?->department ?? '-' }}</td>
                            <td>{{ $teacher->employee?->designation ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $teacher->is_active ? 'success' : 'danger' }}">
                                    {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this teacher?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No teachers found.</td>
                        </tr>
                        @endforelse
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
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#teachersTable')) {
            // If already initialized, destroy it first
            $('#teachersTable').DataTable().destroy();
        }
        
        // Initialize DataTable with options
        $('#teachersTable').DataTable({
            paging: false,      // Disable DataTables pagination since we're using Laravel's pagination
            searching: false,   // Disable DataTables search since we have custom search
            info: false,        // Disable info display
            ordering: true,     // Enable column sorting
            order: [[0, 'desc']], // Default sort by Employee ID
            columnDefs: [
                { orderable: false, targets: [1, 7] }, // Disable sorting on photo and actions columns
                { searchable: false, targets: [1, 7] }  // Disable search on photo and actions columns
            ],
            language: {
                emptyTable: "No teachers found",
                zeroRecords: "No matching teachers found"
            }
        });
    });
</script>
@endpush