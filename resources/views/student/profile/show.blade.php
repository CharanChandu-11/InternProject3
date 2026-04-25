{{-- resources/views/student/profile/show.blade.php --}}
@extends('layouts.student')

@section('title', 'My Profile')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4361ee;">
                        <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" 
                                data-bs-toggle="modal" data-bs-target="#photoModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h3 class="mt-3">{{ $user->name }}</h3>
                    <p class="text-muted">
                        <i class="fas fa-graduation-cap me-1"></i> 
                        {{ $student->class->name ?? 'N/A' }} - Section {{ $student->section->name ?? 'N/A' }}
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-id-card me-1"></i> 
                        Admission No: {{ $student->admission_number }}
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-hashtag me-1"></i> 
                        Roll No: {{ $student->roll_number ?? 'N/A' }}
                    </p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit me-1"></i> Edit Profile
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i> Quick Stats
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted">Attendance</label>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $attendanceSummary['percentage'] }}%</span>
                            <div class="progress flex-grow-1 mx-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $attendanceSummary['percentage'] }}%"></div>
                            </div>
                            <span class="small">{{ $attendanceSummary['present'] }}/{{ $attendanceSummary['total_days'] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted">Average Marks</label>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ round($performanceSummary['average_percentage'], 2) }}%</span>
                            <div class="progress flex-grow-1 mx-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $performanceSummary['average_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-muted">Total Exams</label>
                        <p class="mb-0"><strong>{{ $performanceSummary['total_exams'] }}</strong> exams taken</p>
                    </div>
                    @if($performanceSummary['best_subject'])
                        <div class="mt-2">
                            <label class="text-muted">Best Subject</label>
                            <p class="mb-0"><strong>{{ $performanceSummary['best_subject']['name'] }}</strong> ({{ round($performanceSummary['best_subject']['average'], 2) }}%)</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Parents Information -->
            @if($parents->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-users me-2"></i> Parents / Guardians
                </div>
                <div class="card-body">
                    @foreach($parents as $parent)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ ucfirst($parent->pivot->relationship) }}</strong>
                            <p class="mb-0">{{ $parent->name }}</p>
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-phone me-1"></i> {{ $parent->phone ?? 'N/A' }}
                                <br>
                                <i class="fas fa-envelope me-1"></i> {{ $parent->email ?? 'N/A' }}
                            </p>
                            @if($parent->parent?->occupation)
                                <small class="text-muted">Occupation: {{ $parent->parent->occupation }}</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        
        <!-- Personal Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-circle me-2"></i> Personal Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Full Name</label>
                            <p class="fw-bold mb-0">{{ $user->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Email Address</label>
                            <p class="fw-bold mb-0">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Phone Number</label>
                            <p class="fw-bold mb-0">{{ $user->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Date of Birth</label>
                            <p class="fw-bold mb-0">{{ $profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Gender</label>
                            <p class="fw-bold mb-0">{{ ucfirst($profile?->gender ?? 'N/A') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Blood Group</label>
                            <p class="fw-bold mb-0">{{ $profile?->blood_group ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Address</label>
                            <p class="fw-bold mb-0">{{ $user->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Emergency Contact -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-phone-alt me-2"></i> Emergency Contact
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Contact Name</label>
                            <p class="fw-bold mb-0">{{ $profile?->emergency_contact_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted">Contact Number</label>
                            <p class="fw-bold mb-0">{{ $profile?->emergency_contact ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted">Medical Conditions (if any)</label>
                            <p class="fw-bold mb-0">{{ $profile?->medical_conditions ?? 'None' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Academic Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-graduation-cap me-2"></i> Academic Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Admission Date</label>
                            <p class="fw-bold mb-0">{{ $student->admission_date->format('F j, Y') }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Previous School</label>
                            <p class="fw-bold mb-0">{{ $student->previous_school ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Previous Grade</label>
                            <p class="fw-bold mb-0">{{ $student->previous_grade ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $profile?->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-control">
                                <option value="">Select Blood Group</option>
                                <option value="A+" {{ old('blood_group', $profile?->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_group', $profile?->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_group', $profile?->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_group', $profile?->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_group', $profile?->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_group', $profile?->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_group', $profile?->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_group', $profile?->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $profile?->emergency_contact_name) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Emergency Contact Number</label>
                            <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $profile?->emergency_contact) }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Medical Conditions</label>
                            <textarea name="medical_conditions" class="form-control" rows="2">{{ old('medical_conditions', $profile?->medical_conditions) }}</textarea>
                            <small class="text-muted">List any allergies, chronic conditions, or special medical needs</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.profile.change-password') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control" required>
                        @error('current_password')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img src="{{ $user->profile_photo_url }}" alt="Current Photo" 
                             style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select New Photo</label>
                        <input type="file" name="profile_photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Max size: 2MB. Supported formats: JPG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    label.text-muted {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .fw-bold {
        font-size: 16px;
    }
</style>
@endpush