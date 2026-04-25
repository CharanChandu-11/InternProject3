{{-- resources/views/admin/sections/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Section')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Section: {{ $section->class->full_name }} - {{ $section->name }}
            <div class="float-end">
                <a href="{{ route('admin.sections.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sections.update', $section) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Section Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $section->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Class <span class="text-danger">*</span>
                        </label>
                        <select name="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $section->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->full_name }} ({{ $class->academicYear->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Capacity</label>
                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                               value="{{ old('capacity', $section->capacity) }}" min="1">
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Current Students</label>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-users me-2"></i>
                            <strong>{{ $section->students()->count() }}</strong> students currently enrolled
                            @if($section->capacity)
                                @php
                                    $percentage = ($section->students()->count() / $section->capacity) * 100;
                                @endphp
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-{{ $percentage >= 90 ? 'danger' : ($percentage >= 75 ? 'warning' : 'success') }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($percentage, 1) }}% of capacity used</small>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Caution:</strong> Changing the section name or class will affect existing student records. Students will need to be reassigned if the class changes.
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Section
                    </button>
                    <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection