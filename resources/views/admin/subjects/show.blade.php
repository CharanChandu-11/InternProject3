{{-- resources/views/admin/subjects/show.blade.php --}}
@extends('layouts.admin')

@section('title', $subject->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book me-2"></i> Subject Details: {{ $subject->name }}
            <div class="float-end">
                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Subject Code</th>
                            <td><span class="badge bg-primary fs-6">{{ $subject->code }}</span></td>
                        </tr>
                        <tr>
                            <th>Subject Name</th>
                            <td>{{ $subject->name }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                <span class="badge bg-{{ $subject->type == 'core' ? 'primary' : ($subject->type == 'elective' ? 'info' : ($subject->type == 'language' ? 'success' : 'warning')) }}">
                                    {{ ucfirst($subject->type) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $subject->description ?? 'No description provided.' }}</td>
                        </tr>
                        <tr>
                            <th>Assigned Classes</th>
                            <td>
                                @if($subject->classes->count() > 0)
                                    <span class="badge bg-success">{{ $subject->classes->count() }} Class(es)</span>
                                @else
                                    <span class="badge bg-secondary">Not assigned to any class</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="mt-4">
                <h5><i class="fas fa-chalkboard-user me-2"></i> Classes Teaching This Subject</h5>
                @if($subject->classes->count() > 0)
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Teacher</th>
                                    <th>Theory Marks</th>
                                    <th>Practical Marks</th>
                                    <th>Lab Required</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subject->classes as $class)
                                    <tr>
                                        <td>{{ $class->name }}</td>
                                        <td>
                                            @foreach($class->sections as $section)
                                                <span class="badge bg-info me-1">{{ $section->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @php
                                                $teacher = \App\Models\User::find($class->pivot->teacher_id);
                                            @endphp
                                            {{ $teacher?->name ?? 'Not Assigned' }}
                                        </td>
                                        <td>{{ $class->pivot->theory_marks }}</td>
                                        <td>{{ $class->pivot->practical_marks }}</td>
                                        <td>
                                            <span class="badge bg-{{ $class->pivot->is_lab_required ? 'success' : 'secondary' }}">
                                                {{ $class->pivot->is_lab_required ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.subjects.remove-from-class', $subject) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="class_id" value="{{ $class->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this subject from {{ $class->name }}?')">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i> This subject is not assigned to any class yet.
                    </div>
                @endif
            </div>
            
            <div class="mt-4">
                <h5><i class="fas fa-plus-circle me-2"></i> Assign to Class</h5>
                <form action="{{ route('admin.subjects.assign-to-class') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="class_id" class="form-label fw-semibold mb-2">
                                <i class="fas fa-graduation-cap me-1 text-primary"></i> Select Class <span class="text-danger">*</span>
                            </label>
                            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                                <option value="">-- Choose Class --</option>
                                @foreach(\App\Models\Classes::with('sections')->get() as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} 
                                        @if($class->sections->count() > 0)
                                            ({{ $class->sections->pluck('name')->implode(', ') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select the class to assign this subject to</small>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="teacher_id" class="form-label fw-semibold mb-2">
                                <i class="fas fa-chalkboard-user me-1 text-primary"></i> Select Teacher <span class="text-danger">*</span>
                            </label>
                            <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">-- Choose Teacher --</option>
                                @foreach(\App\Models\User::where('user_type', 'teacher')->with('employee')->get() as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->name }} 
                                        @if($teacher->employee)
                                            ({{ $teacher->employee->designation ?? 'Teacher' }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select the teacher who will teach this subject</small>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="theory_marks" class="form-label fw-semibold mb-2">
                                <i class="fas fa-pen-alt me-1 text-primary"></i> Theory Marks
                            </label>
                            <input type="number" name="theory_marks" id="theory_marks" 
                                class="form-control @error('theory_marks') is-invalid @enderror" 
                                placeholder="Max theory marks" value="{{ old('theory_marks', 100) }}" min="0" max="100">
                            @error('theory_marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum theory marks (0-100)</small>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="practical_marks" class="form-label fw-semibold mb-2">
                                <i class="fas fa-flask me-1 text-primary"></i> Practical Marks
                            </label>
                            <input type="number" name="practical_marks" id="practical_marks" 
                                class="form-control @error('practical_marks') is-invalid @enderror" 
                                placeholder="Max practical marks" value="{{ old('practical_marks', 0) }}" min="0" max="100">
                            @error('practical_marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum practical marks (0-100)</small>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-check mt-4 pt-2">
                                <input type="checkbox" name="is_lab_required" class="form-check-input" value="1" id="labRequired" {{ old('is_lab_required') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="labRequired">
                                    <i class="fas fa-microscope me-1 text-primary"></i> Lab Required
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Check if this subject requires lab sessions</small>
                        </div>
                        
                        <div class="col-md-12">
                            <hr class="my-3">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-link me-2"></i> Assign Subject to Class
                            </button>
                            <button type="reset" class="btn btn-secondary px-4 ms-2">
                                <i class="fas fa-undo me-2"></i> Reset Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection