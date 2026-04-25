{{-- resources/views/admin/subjects/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Subjects')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book me-2"></i> Subject Management
            <div class="float-end">
                <a href="{{ route('admin.subjects.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Subject
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="core" {{ request('type') == 'core' ? 'selected' : '' }}>Core</option>
                            <option value="elective" {{ request('type') == 'elective' ? 'selected' : '' }}>Elective</option>
                            <option value="language" {{ request('type') == 'language' ? 'selected' : '' }}>Language</option>
                            <option value="practical" {{ request('type') == 'practical' ? 'selected' : '' }}>Practical</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Subject Name</th>
                            <th>Type</th>
                            <th>Assigned Classes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                        <tr>
                            <td class="fw-bold">{{ $subject->code }}</td>
                            <td>{{ $subject->name }}</td>
                            <td>
                                <span class="badge bg-{{ $subject->type == 'core' ? 'primary' : ($subject->type == 'elective' ? 'info' : ($subject->type == 'language' ? 'success' : 'warning')) }}">
                                    {{ ucfirst($subject->type) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $classCount = $subject->classes()->count();
                                @endphp
                                @if($classCount > 0)
                                    <span class="badge bg-info">{{ $classCount }} Class(es)</span>
                                @else
                                    <span class="badge bg-secondary">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-book-open me-2"></i> No subjects found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $subjects->links() }}
        </div>
    </div>
</div>
@endsection