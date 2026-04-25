{{-- resources/views/student/profile.blade.php --}}
@extends('layouts.student')

@section('title', 'My Profile')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-4">
            <div class="card text-center" data-aos="fade-right">
                <div class="card-body">
                    <div class="position-relative d-inline-block">
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="Profile" 
                             class="rounded-circle mb-3 border border-4 border-primary" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-2 border border-white">
                            <i class="fas fa-check fa-xs text-white"></i>
                        </div>
                    </div>
                    <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                    <p class="text-muted mb-3">{{ Auth::user()->email }}</p>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Admission No</small>
                                <h6 class="mb-0">{{ $student->admission_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Roll Number</small>
                                <h6 class="mb-0">{{ $student->roll_number }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Class</small>
                                <h6 class="mb-0">{{ $student->class->name }}</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted">Section</small>
                                <h6 class="mb-0">{{ $student->section->name }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4" data-aos="fade-right" data-aos-delay="100">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Quick Info
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Phone</span>
                        <span class="fw-bold">{{ Auth::user()->phone ?? 'Not set' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Date of Birth</span>
                        <span class="fw-bold">{{ Auth::user()->profile?->date_of_birth?->format('d M, Y') ?? 'Not set' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Blood Group</span>
                        <span class="fw-bold">{{ Auth::user()->profile?->blood_group ?? 'Not set' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Gender</span>
                        <span class="fw-bold">{{ ucfirst(Auth::user()->profile?->gender ?? 'Not set') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card" data-aos="fade-left">
                <div class="card-header">
                    <i class="fas fa-edit"></i> Edit Profile
                </div>
                <div class="card-body">
                    <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ Auth::user()->phone }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ Auth::user()->profile?->date_of_birth?->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ Auth::user()->profile?->gender == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ Auth::user()->profile?->gender == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ Auth::user()->profile?->gender == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Blood Group</label>
                                <input type="text" name="blood_group" class="form-control" value="{{ Auth::user()->profile?->blood_group }}">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea name="address" class="form-control" rows="3">{{ Auth::user()->address }}</textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                                <small class="text-muted">Leave blank to keep current photo</small>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4" data-aos="fade-left" data-aos-delay="100">
                <div class="card-header">
                    <i class="fas fa-key"></i> Change Password
                </div>
                <div class="card-body">
                    <form action="{{ route('student.profile.change-password') }}" method="POST">
                        @csrf
                        @method('POST')
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection