@extends('layouts.admin')

@section('title', 'Edit Teacher')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chalkboard-user me-2"></i> Edit Teacher: {{ $teacher->name }}
        </div>
        <div class="card-body">
            <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <ul class="nav nav-tabs mb-4" id="teacherTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">Personal Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="professional-tab" data-bs-toggle="tab" data-bs-target="#professional" type="button">Professional Details</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button">Account Details</button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Personal Details Tab -->
                    <div class="tab-pane fade show active" id="personal">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $teacher->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $teacher->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $teacher->phone) }}" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Emergency Contact</label>
                                <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $teacher->profile?->emergency_contact) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $teacher->profile?->date_of_birth?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $teacher->profile?->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $teacher->profile?->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $teacher->profile?->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $teacher->address) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Profile Photo</label>
                                @if($teacher->profile_photo)
                                    <div class="mb-2">
                                        <img src="{{ $teacher->profile_photo_url }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                @endif
                                <input type="file" name="profile_photo" class="form-control">
                                <small class="text-muted">Leave empty to keep current photo.</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Professional Details Tab -->
                    <div class="tab-pane fade" id="professional">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Department</label>
                                <select name="department" class="form-control">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ old('department', $teacher->employee?->department) == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Designation</label>
                                <select name="designation" class="form-control">
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $desig)
                                        <option value="{{ $desig }}" {{ old('designation', $teacher->employee?->designation) == $desig ? 'selected' : '' }}>{{ $desig }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Employment Type</label>
                                <select name="employment_type" class="form-control">
                                    <option value="">Select Type</option>
                                    @foreach($employmentTypes as $key => $type)
                                        <option value="{{ $key }}" {{ old('employment_type', $teacher->employee?->employment_type) == $key ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Joining Date</label>
                                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $teacher->employee?->joining_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Qualification</label>
                                <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $teacher->profile?->qualification) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Experience (Years)</label>
                                <input type="number" name="experience_years" class="form-control" value="{{ old('experience_years', $teacher->profile?->experience_years ?? 0) }}" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Salary (₹)</label>
                                <input type="number" name="salary" class="form-control" value="{{ old('salary', $teacher->employee?->salary) }}" step="any" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ old('is_active', $teacher->is_active) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $teacher->is_active) ? '' : 'selected' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Details Tab -->
                    <div class="tab-pane fade" id="account">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>New Password (optional)</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i> Leave password fields empty to keep current password.
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Teacher</button>
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection