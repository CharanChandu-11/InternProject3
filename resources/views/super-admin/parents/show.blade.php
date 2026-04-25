{{-- resources/views/super-admin/parents/show.blade.php --}}
@extends('layouts.super-admin')

@section('title', 'Parent Details - ' . $parent->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-friends me-2"></i> Parent Details: {{ $parent->name }}
            <div class="float-end">
                <a href="{{ route('super-admin.parents.edit', $parent) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('super-admin.parents.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Profile Section -->
                <div class="col-md-3 text-center">
                    <img src="{{ $parent->profile_photo_url }}" alt="{{ $parent->name }}" 
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4361ee;">
                    <h4 class="mt-3">{{ $parent->name }}</h4>
                    <p class="text-muted">{{ ucfirst(str_replace('_', ' ', $parent->parent->parent_type ?? 'N/A')) }}</p>
                    <span class="badge bg-{{ $parent->is_active ? 'success' : 'danger' }} fs-6">
                        {{ $parent->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <!-- Personal Details -->
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-envelope me-2 text-primary"></i> Email</h6>
                                <p>{{ $parent->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-phone me-2 text-primary"></i> Phone</h6>
                                <p>{{ $parent->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-phone-alt me-2 text-primary"></i> Alternative Phone</h6>
                                <p>{{ $parent->profile?->alternative_phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Date of Birth</h6>
                                <p>{{ $parent->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-venus-mars me-2 text-primary"></i> Gender</h6>
                                <p>{{ ucfirst($parent->profile?->gender ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-graduation-cap me-2 text-primary"></i> Qualification</h6>
                                <p>{{ $parent->profile?->qualification ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-box">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address</h6>
                                <p>{{ $parent->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Professional Details -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Professional Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Occupation:</strong> {{ $parent->parent->occupation ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Office Address:</strong> {{ $parent->parent->office_address ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Office Phone:</strong> {{ $parent->parent->office_phone ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Annual Income:</strong> {{ $parent->parent->annual_income ? '₹ ' . number_format($parent->parent->annual_income, 2) : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Children Information -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Children (Wards)</h5>
                    @if(count($children) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Admission No</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Roll No</th>
                                        <th>Attendance</th>
                                        <th>Fee Due</th>
                                        <th>Relationship</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($children as $child)
                                    <tr>
                                        <td>{{ $child['name'] }}</td>
                                        <td>{{ $child['admission_number'] }}</td>
                                        <td>{{ $child['class'] }}</td>
                                        <td>{{ $child['section'] }}</td>
                                        <td>{{ $child['roll_number'] }}</td>
                                        <td>{{ $child['attendance_percentage'] }}%</td>
                                        <td>₹ {{ number_format($child['fee_due'], 2) }}</td>
                                        <td>
                                            {{ ucfirst($child['relationship']) }}
                                            @if($child['is_primary_contact'])
                                                <span class="badge bg-primary">Primary</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No children linked.</p>
                    @endif
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Recent Activities</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Action</th><th>Module</th><th>Description</th><th>Time</th></tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                <tr>
                                    <td>{{ ucfirst($activity->action) }}</td>
                                    <td>{{ ucfirst($activity->module) }}</td>
                                    <td>{{ $activity->description }}</td>
                                    <td>{{ $activity->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .info-box h6 {
        margin-bottom: 8px;
    }
    .info-box p {
        margin-bottom: 0;
        color: #6c757d;
    }
</style>
@endpush