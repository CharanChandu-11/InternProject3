{{-- resources/views/super-admin/subjects/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Subject Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book me-2"></i> Subject Management
            <div class="float-end">
                <a href="{{ route('super-admin.subjects.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Subject
                </a>
                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                    <i class="fas fa-upload me-1"></i> Bulk Import
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($subjectTypes as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, code, or description..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.subjects.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Subjects Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Classes Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                        <tr>
                            <td><strong>{{ $subject->code }}</strong></td>
                            <td>{{ $subject->name }}</td>
                            <td>
                                <span class="badge bg-{{ $subject->type == 'core' ? 'primary' : ($subject->type == 'elective' ? 'info' : ($subject->type == 'language' ? 'success' : 'warning')) }}">
                                    {{ ucfirst($subject->type) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($subject->description, 50) ?? '-' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $subject->classes()->count() }} class(es)</span>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.subjects.show', $subject) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.subjects.edit', $subject) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('super-admin.subjects.destroy', $subject) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            {{ $subject->classes()->count() > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $subjects->links() }}
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('super-admin.subjects.bulk-import') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Import Subjects</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Format:</strong> Name, Code, Type, Description<br>
                        <strong>Example:</strong> Mathematics, MATH101, core, Basic mathematics course<br>
                        <strong>Types:</strong> core, elective, language, practical<br>
                        <strong>One subject per line.</strong>
                    </div>
                    <textarea name="subjects" class="form-control" rows="10" placeholder="Mathematics, MATH101, core, Basic mathematics&#10;English, ENG101, language, English language course&#10;Physics, PHY101, core, Physics with lab" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection