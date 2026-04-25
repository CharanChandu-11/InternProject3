{{-- resources/views/admin/syllabi/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Syllabus Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open me-2"></i> Syllabus Management
            <div class="float-end">
                <a href="{{ route('admin.syllabi.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Syllabus
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(\App\Models\Syllabus::getStatuses() as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Academic Year</th>
                            <th>Status</th>
                            <th>Topics</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syllabi as $syllabus)
                        <tr>
                            <td>{{ $syllabus->id }}</td>
                            <td>{{ $syllabus->title }} <br> <small class="text-muted">{{ Str::limit($syllabus->description, 50) }}</small></td>
                            <td>{{ $syllabus->class->name }}</td>
                            <td>{{ $syllabus->subject->name }} ({{ $syllabus->subject->code }})</span></td>
                            <td>{{ $syllabus->academicYear->name }}</td>
                            <td>
                                <span class="badge bg-{{ $syllabus->status == 'published' ? 'success' : ($syllabus->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($syllabus->status) }}
                                </span>
                            </td>
                            <td>{{ $syllabus->topics->count() }}</td>
                            <td>{{ $syllabus->creator->name }}</td>
                            <td>
                                <a href="{{ route('admin.syllabi.show', $syllabus) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.syllabi.edit', $syllabus) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.syllabi.destroy', $syllabus) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
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

            {{ $syllabi->links() }}
        </div>
    </div>
</div>
@endsection