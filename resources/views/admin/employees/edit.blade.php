{{-- resources/views/admin/employees/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Employee')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-edit me-2"></i> Edit Employee: {{ $employee->user->name }}
            <div class="float-end">
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <i class="fas fa-user-circle me-1"></i> Personal Information
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->user->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->user->email) }}" required>
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->user->phone) }}" required>
                                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($employee->user->profile)->date_of_birth?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-control">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('gender', optional($employee->user->profile)->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', optional($employee->user->profile)->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender', optional($employee->user->profile)->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->user->address) }}</textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Profile Photo</label>
                                    @if($employee->user->profile_photo)
                                        <div class="mb-2">
                                            <img src="{{ $employee->user->profile_photo_url }}" alt="" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                                        </div>
                                    @endif
                                    <input type="file" name="profile_photo" class="form-control">
                                    <small class="text-muted">Leave blank to keep current photo. Max 2MB, JPG, PNG</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <i class="fas fa-briefcase me-1"></i> Employment Information
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Employee ID <span class="text-danger">*</span></label>
                                        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id', $employee->employee_id) }}" required>
                                        @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                                        <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $employee->joining_date->format('Y-m-d')) }}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                        <select name="department" class="form-control" required>
                                            <option value="Academic" {{ old('department', $employee->department) == 'Academic' ? 'selected' : '' }}>Academic</option>
                                            <option value="Administration" {{ old('department', $employee->department) == 'Administration' ? 'selected' : '' }}>Administration</option>
                                            <option value="Accounts" {{ old('department', $employee->department) == 'Accounts' ? 'selected' : '' }}>Accounts</option>
                                            <option value="IT" {{ old('department', $employee->department) == 'IT' ? 'selected' : '' }}>IT</option>
                                            <option value="HR" {{ old('department', $employee->department) == 'HR' ? 'selected' : '' }}>HR</option>
                                            <option value="Transport" {{ old('department', $employee->department) == 'Transport' ? 'selected' : '' }}>Transport</option>
                                            <option value="Hostel" {{ old('department', $employee->department) == 'Hostel' ? 'selected' : '' }}>Hostel</option>
                                            <option value="Library" {{ old('department', $employee->department) == 'Library' ? 'selected' : '' }}>Library</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                                        <input type="text" name="designation" class="form-control" value="{{ old('designation', $employee->designation) }}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                                        <select name="employment_type" class="form-control" required>
                                            <option value="full_time" {{ old('employment_type', $employee->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                            <option value="part_time" {{ old('employment_type', $employee->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                            <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                            <option value="probation" {{ old('employment_type', $employee->employment_type) == 'probation' ? 'selected' : '' }}>Probation</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Salary</label>
                                        <input type="number" name="salary" class="form-control" value="{{ old('salary', $employee->salary) }}" step="0.01">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Qualification</label>
                                    <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $employee->qualification) }}">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Experience (Years)</label>
                                    <input type="number" name="experience_years" class="form-control" value="{{ old('experience_years', $employee->experience_years) }}" step="0.5">
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <i class="fas fa-university me-1"></i> Bank Information
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $employee->bank_name) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account', $employee->bank_account) }}">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">IFSC Code</label>
                                        <input type="text" name="ifsc_code" class="form-control" value="{{ old('ifsc_code', $employee->ifsc_code) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">PAN Number</label>
                                        <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $employee->pan_number) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-light">
                                <i class="fas fa-key me-1"></i> Login Credentials
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="{{ old('username', $employee->user->username) }}" readonly>
                                        <small class="text-muted">Username cannot be changed</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="password" class="form-control">
                                        <small class="text-muted">Leave blank to keep current password</small>
                                    </div>
                                </div>
                                
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', $employee->user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Account Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i> Update Employee
                    </button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary btn-lg px-5">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection