{{-- resources/views/super-admin/parents/create.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Add New Parent')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-plus me-2"></i> Add New Parent
        </div>
        <div class="card-body">
            <form action="{{ route('super-admin.parents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <ul class="nav nav-tabs mb-3" id="parentTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal" type="button">Personal Info</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#professional" type="button">Professional Info</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#children" type="button">Children</button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Personal Info Tab -->
                    <div class="tab-pane fade show active" id="personal">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" 
                                       value="{{ old('username') }}" required>
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alternative Phone</label>
                                <input type="text" name="alternative_phone" class="form-control" value="{{ old('alternative_phone') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Qualification</label>
                                <input type="text" name="qualification" class="form-control" value="{{ old('qualification') }}">
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Professional Info Tab -->
                    <div class="tab-pane fade" id="professional">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Parent Type <span class="text-danger">*</span></label>
                                <select name="parent_type" class="form-control @error('parent_type') is-invalid @enderror" required>
                                    <option value="">Select Parent Type</option>
                                    @foreach($parentTypes as $type)
                                        <option value="{{ $type }}" {{ old('parent_type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" name="occupation" class="form-control" value="{{ old('occupation') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Office Address</label>
                                <input type="text" name="office_address" class="form-control" value="{{ old('office_address') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Office Phone</label>
                                <input type="text" name="office_phone" class="form-control" value="{{ old('office_phone') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Annual Income</label>
                                <input type="number" name="annual_income" class="form-control" step="0.01" value="{{ old('annual_income') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact</label>
                                <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact') }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Children Tab -->
                    <div class="tab-pane fade" id="children">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Select Children (Students)</label>
                                <select name="child_ids[]" class="form-control" multiple size="8">
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ in_array($student->id, old('child_ids', [])) ? 'selected' : '' }}>
                                            {{ $student->user->name }} - {{ $student->admission_number }} 
                                            ({{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl to select multiple children</small>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_primary_contact" class="form-check-input" value="1" {{ old('is_primary_contact') ? 'checked' : '' }}>
                                    <label class="form-check-label">Set as Primary Contact for selected children</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Parent
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