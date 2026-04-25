{{-- resources/views/super-admin/teachers/import.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Import Teachers')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-upload me-2"></i> Import Teachers
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> 
                Please upload an Excel file (.xlsx or .csv) with the following columns:
                <ul class="mt-2 mb-0">
                    <li><strong>name</strong> - Full name (required)</li>
                    <li><strong>email</strong> - Email address (required, unique)</li>
                    <li><strong>username</strong> - Username (required, unique)</li>
                    <li><strong>phone</strong> - Phone number (required)</li>
                    <li><strong>date_of_birth</strong> - Date of birth (YYYY-MM-DD)</li>
                    <li><strong>gender</strong> - Gender (male/female/other)</li>
                    <li><strong>address</strong> - Address</li>
                    <li><strong>qualification</strong> - Educational qualification</li>
                    <li><strong>experience_years</strong> - Years of experience</li>
                    <li><strong>department</strong> - Department (required)</li>
                    <li><strong>designation</strong> - Designation (required)</li>
                    <li><strong>employment_type</strong> - full_time/part_time/contract</li>
                    <li><strong>joining_date</strong> - Joining date (YYYY-MM-DD)</li>
                    <li><strong>salary</strong> - Monthly salary</li>
                </ul>
                <p class="mt-2 mb-0 text-warning">Note: Default password will be set to 'password123' for all imported teachers.</p>
            </div>
            
            <form action="{{ route('super-admin.teachers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" 
                           accept=".xlsx,.csv" required>
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('super-admin.teachers.export') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Download Sample Template
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import Teachers
                    </button>
                    <a href="{{ route('super-admin.teachers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection