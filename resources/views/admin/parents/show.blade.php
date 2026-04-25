{{-- resources/views/admin/parents/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Parent Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-circle me-2"></i> Parent Details: {{ $parent->name }}
            <div class="float-end">
                <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.parents.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="{{ $parent->profile_photo_url }}" alt="Profile Photo" 
                         class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <h4>{{ $parent->name }}</h4>
                    <span class="badge bg-{{ $parent->is_active ? 'success' : 'danger' }} mb-2">
                        {{ $parent->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Email</label>
                                <p class="fw-bold">{{ $parent->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Phone Number</label>
                                <p class="fw-bold">{{ $parent->phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Parent Type</label>
                                <p class="fw-bold">
                                    <span class="badge bg-{{ $parent->parent->parent_type == 'father' ? 'info' : ($parent->parent->parent_type == 'mother' ? 'primary' : 'secondary') }}">
                                        {{ ucfirst($parent->parent->parent_type) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Occupation</label>
                                <p class="fw-bold">{{ $parent->parent->occupation ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Date of Birth</label>
                                <p class="fw-bold">{{ optional($parent->profile)->date_of_birth ? optional($parent->profile)->date_of_birth->format('F j, Y') : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Gender</label>
                                <p class="fw-bold">{{ optional($parent->profile)->gender ? ucfirst(optional($parent->profile)->gender) : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Address</label>
                                <p class="fw-bold">{{ $parent->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Office Address</label>
                                <p class="fw-bold">{{ $parent->parent->office_address ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Office Phone</label>
                                <p class="fw-bold">{{ $parent->parent->office_phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-card mb-3">
                                <label class="text-muted mb-1">Username</label>
                                <p class="fw-bold">{{ $parent->username }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h5><i class="fas fa-child me-2"></i> Children</h5>
            <div class="table-responsive mt-3">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Roll No</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parent->parent->children as $child)
                        <tr>
                            <td>
                                <img src="{{ $child->user->profile_photo_url }}" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </td>
                            <td class="fw-bold">{{ $child->user->name }}</td>
                            <td>{{ $child->admission_number }}</td>
                            <td>{{ $child->class->name ?? 'N/A' }}</td>
                            <td>{{ $child->section->name ?? 'N/A' }}</td>
                            <td>{{ $child->roll_number ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.students.show', $child) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-child fa-2x mb-2 d-block"></i>
                                No children associated.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection