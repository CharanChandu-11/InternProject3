{{-- resources/views/super-admin/users/import.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Import Users')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-upload me-2"></i> Import Users
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
                    <li><strong>user_type</strong> - User type (super_admin, admin, teacher, employee, parent, student)</li>
                    <li><strong>date_of_birth</strong> - Date of birth (optional)</li>
                    <li><strong>gender</strong> - Gender (male/female/other)</li>
                    <li><strong>address</strong> - Address (optional)</li>
                </ul>
            </div>
            
            <form action="{{ route('super-admin.users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Select File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" 
                           accept=".xlsx,.csv" required>
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <a href="{{ route('super-admin.users.export') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Download Sample Template
                    </a>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i> Import Users
                </button>
                <a href="{{ route('super-admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection