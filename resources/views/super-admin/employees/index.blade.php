{{-- resources/views/super-admin/employees/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Employee Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-tie me-2"></i> Employee Management
            <div class="float-end">
                <a href="{{ route('super-admin.employees.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Employee
                </a>
                <a href="{{ route('super-admin.employees.import-form') }}" class="btn btn-sm btn-info">
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
                        <select name="employment_type" class="form-select">
                            <option value="">All Employment Types</option>
                            @foreach($employmentTypes as $type)
                                <option value="{{ $type }}" {{ request('employment_type') == $type ? 'selected' : '' }}>
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
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, employee ID..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('super-admin.employees.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Employees Table -->
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
                            <th>Employment Type</th>
                            <th>Joining Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td><strong>{{ $employee->employee->employee_id ?? 'N/A' }}</strong></td>
                            <td>
                                <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->name }}" 
                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td>{{ $employee->name }}<br>
                                <small class="text-muted">Since: {{ $employee->employee->joining_date->format('M Y') ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->employee->department ?? 'N/A' }}</td>
                            <td>{{ $employee->employee->designation ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $employee->employee->employment_type == 'full_time' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $employee->employee->employment_type ?? 'N/A')) }}
                                </span>
                            </td>
                            <td>{{ $employee->employee->joining_date->format('d-m-Y') ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }}">
                                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.employees.show', $employee) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.employees.edit', $employee) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('super-admin.employees.toggle-status', $employee) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $employee->is_active ? 'warning' : 'success' }}">
                                        <i class="fas fa-{{ $employee->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('super-admin.employees.destroy', $employee) }}" method="POST" class="d-inline delete-form">
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
            
            {{ $employees->links() }}
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