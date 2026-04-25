{{-- resources/views/parent/children/show.blade.php --}}
@extends('layouts.parent')

@section('title', $student->user->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-graduate me-2"></i> Student Details: {{ $student->user->name }}
            <div class="float-end">
                <a href="{{ route('parent.children') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="{{ $student->user->profile_photo_url }}" alt="Photo" 
                         class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <h4>{{ $student->user->name }}</h4>
                    <p class="text-muted">{{ $student->admission_number }}</p>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-envelope me-2 text-primary"></i> Email</h6>
                                <p>{{ $student->user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-phone me-2 text-primary"></i> Phone</h6>
                                <p>{{ $student->user->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Date of Birth</h6>
                                <p>{{ $student->user->profile?->date_of_birth?->format('F j, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <h6><i class="fas fa-tint me-2 text-primary"></i> Blood Group</h6>
                                <p>{{ $student->user->profile?->blood_group ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-box">
                                <h6><i class="fas fa-map-marker-alt me-2 text-primary"></i> Address</h6>
                                <p>{{ $student->user->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Academic Information</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Class:</strong> {{ $student->class->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Section:</strong> {{ $student->section->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Roll Number:</strong> {{ $student->roll_number ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Admission Date:</strong> {{ $student->admission_date->format('d-m-Y') }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="border-bottom pb-2">Parents Information</h5>
                    @if($student->parents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Name</th><th>Relationship</th><th>Email</th><th>Phone</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($student->parents as $parent)
                                    <tr>
                                        <td>{{ $parent->name }}</span></td>
                                        <td>{{ ucfirst($parent->pivot->relationship) }}</span></td>
                                        <td>{{ $parent->email }}</span></td>
                                        <td>{{ $parent->phone }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">No parents linked.</p>
                    @endif
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="btn-group w-100">
                        <a href="{{ route('parent.children.attendance', $student) }}" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </a>
                        <a href="{{ route('parent.children.results', $student) }}" class="btn btn-outline-success">
                            <i class="fas fa-chart-line"></i> Results
                        </a>
                        <a href="{{ route('parent.children.fees', $student) }}" class="btn btn-outline-warning">
                            <i class="fas fa-rupee-sign"></i> Fees
                        </a>
                        <a href="{{ route('parent.children.homework', $student) }}" class="btn btn-outline-info">
                            <i class="fas fa-book-open"></i> Homework
                        </a>
                        <a href="{{ route('parent.children.timetable', $student) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-clock"></i> Timetable
                        </a>
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