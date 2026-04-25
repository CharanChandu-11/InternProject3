{{-- resources/views/admin/sections/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Sections')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-layer-group me-2"></i> Section Management
                <span class="badge bg-primary ms-2">{{ $sections->total() }} Total</span>
            </div>
            <div>
                <a href="{{ route('admin.sections.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Section
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-semibold">CLASS</label>
                        <select name="class_id" class="form-select form-select-sm">
                            <option value="">All Classes</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-semibold">SEARCH</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Section name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary btn-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Sections Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Section</th>
                            <th width="25%">Class</th>
                            <th width="15%">Capacity</th>
                            <th width="20%">Students</th>
                            <th width="15%">Actions</th>
                        </thead>
                        <tbody>
                            @forelse($sections as $section)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="fs-6">Section {{ $section->name }}</strong>
                                        @if($section->capacity)
                                            <small class="text-muted">Max: {{ $section->capacity }} students</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.classes.show', $section->class) }}" class="text-decoration-none">
                                        <i class="fas fa-chalkboard me-1"></i> {{ $section->class->full_name }}
                                    </a>
                                </td>
                                <td>
                                    @if($section->capacity)
                                        <span class="badge bg-info">{{ $section->capacity }}</span>
                                    @else
                                        <span class="text-muted">Unlimited</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-bold">{{ $section->students()->count() }}</span>
                                            @if($section->capacity)
                                                <span class="text-muted small">/ {{ $section->capacity }}</span>
                                            @endif
                                        </div>
                                        @if($section->capacity)
                                            @php
                                                $studentCount = $section->students()->count();
                                                $percentage = ($studentCount / $section->capacity) * 100;
                                                $color = $percentage >= 90 ? 'danger' : ($percentage >= 75 ? 'warning' : 'success');
                                            @endphp
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <small class="text-muted mt-1">{{ number_format($percentage, 1) }}% filled</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.sections.show', $section) }}" class="btn btn-sm btn-info rounded-pill" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-sm btn-primary rounded-pill" title="Edit Section">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.sections.destroy', $section) }}" method="POST">
                                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger rounded-pill" title="Delete Section" onclick="if(confirm('Are you sure you want to delete this section? This action cannot be undone.')) { this.form.submit(); }">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-layer-group fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-2">No sections found</p>
                                    <a href="{{ route('admin.sections.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Add First Section
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
                        Showing {{ $sections->firstItem() ?? 0 }} to {{ $sections->lastItem() ?? 0 }} of {{ $sections->total() }} entries
                    </div>
                    <div>
                        {{ $sections->withQueryString()->links() }}
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