{{-- resources/views/super-admin/parents/import.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Import Parents')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-upload me-2"></i> Import Parents
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
                    <li><strong>alternative_phone</strong> - Alternative phone number</li>
                    <li><strong>date_of_birth</strong> - Date of birth (YYYY-MM-DD)</li>
                    <li><strong>gender</strong> - Gender (male/female/other)</li>
                    <li><strong>address</strong> - Address</li>
                    <li><strong>parent_type</strong> - father/mother/guardian/step_father/step_mother/grandfather/grandmother/uncle/aunt/other</li>
                    <li><strong>occupation</strong> - Occupation</li>
                    <li><strong>office_address</strong> - Office address</li>
                    <li><strong>office_phone</strong> - Office phone number</li>
                    <li><strong>annual_income</strong> - Annual income</li>
                    <li><strong>qualification</strong> - Educational qualification</li>
                    <li><strong>emergency_contact</strong> - Emergency contact number</li>
                </ul>
                <p class="mt-2 mb-0 text-warning">Note: Default password will be set to 'password123' for all imported parents.</p>
                <p class="mt-2 mb-0">Children can be linked after import through the edit screen.</p>
            </div>
            
            <form action="{{ route('super-admin.parents.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" 
                           accept=".xlsx,.csv" required>
                    @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('super-admin.parents.export') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Download Sample Template
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import Parents
                    </button>
                    <a href="{{ route('super-admin.parents.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection