{{-- resources/views/super-admin/academic-years/edit.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Edit Academic Year - ' . $academicYear->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Edit Academic Year: {{ $academicYear->name }}
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.academic-years.update', $academicYear) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Academic Year Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $academicYear->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="is_current" class="form-check-input" value="1" 
                                   {{ old('is_current', $academicYear->is_current) ? 'checked' : '' }}>
                            <label class="form-check-label">Set as Current Academic Year</label>
                        </div>
                        @if($academicYear->is_current)
                            <small class="text-warning">This is currently the active academic year.</small>
                        @endif
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                               value="{{ old('start_date', $academicYear->start_date->format('Y-m-d')) }}" required>
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                               value="{{ old('end_date', $academicYear->end_date->format('Y-m-d')) }}" required>
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Current Duration: {{ $academicYear->start_date->diffInDays($academicYear->end_date) }} days 
                    ({{ $academicYear->start_date->diffInMonths($academicYear->end_date) }} months)
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Academic Year
                    </button>
                    <a href="{{ route('super-admin.academic-years.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection