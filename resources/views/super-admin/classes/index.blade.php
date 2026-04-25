{{-- resources/views/super-admin/classes/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Class Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-building me-2"></i> Class Management
            <div class="float-end">
                <a href="{{ route('super-admin.classes.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Class
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) (Current) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by class name..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.classes.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Classes Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Class Name</th>
                            <th>Academic Year</th>
                            <th>Sections</th>
                            <th>Total Capacity</th>
                            <th>Class Teacher</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classes as $class)
                        <tr>
                            <td>{{ $class->id }}</td>
                            <td>
                                <strong>{{ $class->name }}</strong>
                                @if($class->numeric_name)
                                    <br><small class="text-muted">({{ $class->numeric_name }})</small>
                                @endif
                            </td>
                            <td>{{ $class->academicYear->name ?? 'N/A' }}<br>
                                @if($class->academicYear && $class->academicYear->is_current)
                                    <span class="badge bg-success">Current</span>
                                @endif
                            </td>
                            <td>
                                @foreach($class->sections as $section)
                                    <span class="badge bg-info mb-1">{{ $section->name }}</span>
                                @endforeach
                                <br>
                                <small>{{ $class->sections->count() }} sections</small>
                            </td>
                            <td>
                                {{ $class->capacity * $class->sections->count() }} students
                                <br>
                                <small>({{ $class->capacity }} per section)</small>
                            </td>
                            <td>
                                @if($class->classTeacher)
                                    {{ $class->classTeacher->name }}
                                    <br>
                                    <small class="text-muted">{{ $class->classTeacher->employee->designation ?? 'Teacher' }}</small>
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('super-admin.classes.show', $class) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.classes.edit', $class) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('super-admin.classes.students', $class) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-users"></i>
                                </a>
                                <form action="{{ route('super-admin.classes.destroy', $class) }}" method="POST" class="d-inline delete-form">
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
            
            {{ $classes->links() }}
        </div>
    </div>
</div>
@endsection