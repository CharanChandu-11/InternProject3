{{-- resources/views/super-admin/sections/index.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Section Management')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-layer-group me-2"></i> Section Management
            <div class="float-end">
                <a href="{{ route('super-admin.sections.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Section
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="academic_year_id" class="form-select" id="academicYearFilter">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) (Current) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->academicYear->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by section name or class..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('super-admin.sections.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Bulk Create Form -->
            <div class="card mb-4 bg-light">
                <div class="card-header">
                    <i class="fas fa-layer-group me-2"></i> Bulk Create Sections
                </div>
                <div class="card-body">
                    <form action="{{ route('super-admin.sections.bulk-create') }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label">Select Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sections (comma separated)</label>
                            <input type="text" name="section_names" class="form-control" placeholder="e.g., A, B, C, D" required>
                            <small class="text-muted">Enter section names separated by commas</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Capacity per Section</label>
                            <input type="number" name="capacity" class="form-control" value="40" required>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-success w-100">Create</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Sections Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Class</th>
                            <th>Academic Year</th>
                            <th>Section</th>
                            <th>Capacity</th>
                            <th>Students</th>
                            <th>Utilization</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sections as $section)
                        <tr>
                            <td>{{ $section->id }}</td>
                            <td>
                                <strong>{{ $section->class->name ?? 'N/A' }}</strong>
                            </td>
                            <td>{{ $section->class->academicYear->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-primary fs-6">Section {{ $section->name }}</span>
                            </td>
                            <td>{{ $section->capacity }} students</td>
                            <td>
                                @php
                                    $studentCount = $section->students()->count();
                                    $percentage = $section->capacity > 0 ? ($studentCount / $section->capacity) * 100 : 0;
                                @endphp
                                {{ $studentCount }} students
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ round($percentage) }}%</span>
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success') }}" 
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.sections.show', $section) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('super-admin.sections.edit', $section) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('super-admin.sections.destroy', $section) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            {{ $studentCount > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{ $sections->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Dynamic class filter based on academic year
    $('#academicYearFilter').change(function() {
        var academicYearId = $(this).val();
        if (academicYearId) {
            $.ajax({
                url: '{{ route("super-admin.classes.by-academic-year") }}/' + academicYearId,
                type: 'GET',
                success: function(data) {
                    var classSelect = $('#classFilter');
                    classSelect.empty();
                    classSelect.append('<option value="">All Classes</option>');
                    $.each(data, function(key, classItem) {
                        classSelect.append('<option value="' + classItem.id + '">' + classItem.name + '</option>');
                    });
                }
            });
        }
    });
</script>
@endpush