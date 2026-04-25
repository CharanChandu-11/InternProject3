{{-- resources/views/admin/classes/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Classes')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-chalkboard me-2"></i> Class Management
                <span class="badge bg-primary ms-2">{{ $classes->total() }} Total</span>
            </div>
            <div>
                <a href="{{ route('admin.classes.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Class
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" class="mb-4" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-semibold">ACADEMIC YEAR</label>
                        <select name="academic_year_id" class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-semibold">SEARCH</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Class name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Classes Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Class Name</th>
                            <th width="20%">Academic Year</th>
                            <th width="25%">Class Teacher</th>
                            <th width="15%">Sections</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                        <tr>
                            <td class="text-muted">{{ $class->iteration }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="fs-6">{{ $class->full_name }}</strong>
                                    @if($class->capacity)
                                        <small class="text-muted">Capacity: {{ $class->capacity }} per section</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $class->academicYear->name }}</span>
                            </td>
                            <td>
                                @if($class->classTeacher)
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $class->classTeacher->profile_photo_url }}" 
                                             class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-semibold">{{ $class->classTeacher->name }}</div>
                                            <small class="text-muted">{{ $class->classTeacher->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-user-slash me-1"></i> Not Assigned
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($class->sections as $section)
                                        <span class="badge bg-primary">{{ $section->name }}</span>
                                    @empty
                                        <span class="text-muted small">No sections</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-sm btn-info rounded-pill" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-primary rounded-pill" title="Edit Class">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill" title="Delete Class" onclick="return confirm('Are you sure you want to delete this class? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-2">No classes found</p>
                                <a href="{{ route('admin.classes.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add First Class
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $classes->firstItem() ?? 0 }} to {{ $classes->lastItem() ?? 0 }} of {{ $classes->total() }} entries
                </div>
                <div>
                    {{ $classes->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    .table > :not(caption) > * > * {
        padding: 12px 8px;
        vertical-align: middle;
    }
    .badge {
        font-weight: 500;
        padding: 4px 8px;
    }
    .btn-group .btn {
        margin: 0 2px;
        padding: 0.25rem 0.5rem;
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
    }
</style>
@endpush