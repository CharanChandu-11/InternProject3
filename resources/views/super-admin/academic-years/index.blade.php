{{-- resources/views/super-admin/academic-years/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Academic Year Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> Academic Year Management
            <div class="float-end">
                <a href="{{ route('super-admin.academic-years.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Academic Year
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search by year name..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('super-admin.academic-years.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
            
            <!-- Academic Years Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($academicYears as $year)
                        <tr>
                            <td>{{ $year->id }}</td>
                            <td>
                                <strong>{{ $year->name }}</strong>
                                @if($year->is_current)
                                    <span class="badge bg-success ms-2">Current</span>
                                @endif
                            </td>
                            <td>{{ $year->start_date->format('d-m-Y') }}<br>
                                <small class="text-muted">{{ $year->start_date->format('l') }}</small>
                            </td>
                            <td>{{ $year->end_date->format('d-m-Y') }}<br>
                                <small class="text-muted">{{ $year->end_date->format('l') }}</small>
                            </td>
                            <td>
                                {{ $year->start_date->diffInDays($year->end_date) + 1 }} days
                            </td>
                            <td>
                                @if($year->is_current)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('super-admin.academic-years.show', $year) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.academic-years.edit', $year) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$year->is_current)
                                    <form action="{{ route('super-admin.academic-years.set-current', $year) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check-circle"></i> Set Current
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('super-admin.academic-years.destroy', $year) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            {{ $year->is_current ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $academicYears->links() }}
        </div>
    </div>
</div>
@endsection