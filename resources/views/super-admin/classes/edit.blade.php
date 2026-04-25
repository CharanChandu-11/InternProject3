{{-- resources/views/super-admin/classes/edit.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Edit Class - ' . $class->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Class: {{ $class->name }}
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.classes.update', $class) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $class->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Numeric Name</label>
                        <input type="number" name="numeric_name" class="form-control @error('numeric_name') is-invalid @enderror" 
                               value="{{ old('numeric_name', $class->numeric_name) }}">
                        @error('numeric_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" class="form-control @error('academic_year_id') is-invalid @enderror" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $class->academic_year_id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) (Current) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class Teacher</label>
                        <select name="class_teacher_id" class="form-control @error('class_teacher_id') is-invalid @enderror">
                            <option value="">Select Class Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('class_teacher_id', $class->class_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->employee->designation ?? 'Teacher' }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Section Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                               value="{{ old('capacity', $class->capacity) }}" required>
                        @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Class
                    </button>
                    <a href="{{ route('super-admin.classes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
            
            <!-- Sections Management -->
            <hr class="my-4">
            <h5>Sections Management</h5>
            <div class="table-responsive mt-3">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Section Name</th><th>Capacity</th><th>Students</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($class->sections as $section)
                        <tr>
                            <form action="{{ route('super-admin.classes.edit-section', $section) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <td>
                                    <input type="text" name="name" value="{{ $section->name }}" class="form-control form-control-sm" style="width: 80px;" required>
                                </td>
                                <td>
                                    <input type="number" name="capacity" value="{{ $section->capacity }}" class="form-control form-control-sm" style="width: 100px;" required>
                                </td>
                                <td>{{ \App\Models\Student::where('section_id', $section->id)->count() }} students</td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    <form action="{{ route('super-admin.classes.delete-section', $section) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger delete-btn">Delete</button>
                                    </form>
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Add New Section -->
            <form action="{{ route('super-admin.classes.add-section', $class) }}" method="POST" class="mt-3 row g-2">
                @csrf
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Section Name" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="capacity" class="form-control" placeholder="Capacity" value="40" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection