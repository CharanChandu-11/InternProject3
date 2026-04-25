{{-- resources/views/parent/children.blade.php --}}
@extends('layouts.parent')

@section('title', 'My Children')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-child me-2"></i> My Children
                </div>
                <div class="card-body">
                    @if($children->count() > 0)
                        <div class="row">
                            @foreach($children as $child)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="child-card" onclick="window.location='{{ route('parent.children.show', $child) }}'">
                                        <div class="text-center mb-3">
                                            <img src="{{ $child->user->profile_photo_url }}" alt="{{ $child->user->name }}" class="child-avatar-lg">
                                            <h4 class="mt-3 mb-1">{{ $child->user->name }}</h4>
                                            <p class="text-muted">Class {{ $child->class->name }} - Section {{ $child->section->name }}</p>
                                        </div>
                                        
                                        <div class="row text-center g-2 mb-3">
                                            <div class="col-4">
                                                <div class="bg-light rounded-3 p-2">
                                                    <small class="text-muted">Admission No</small>
                                                    <h6 class="mb-0">{{ $child->admission_number }}</h6>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-light rounded-3 p-2">
                                                    <small class="text-muted">Roll Number</small>
                                                    <h6 class="mb-0">{{ $child->roll_number }}</h6>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="bg-light rounded-3 p-2">
                                                    <small class="text-muted">DOB</small>
                                                    <h6 class="mb-0">{{ $child->user->profile?->date_of_birth?->format('d/m/Y') ?? 'N/A' }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('parent.children.attendance', $child) }}" class="btn btn-outline-primary btn-sm flex-grow-1">
                                                <i class="fas fa-calendar-check me-1"></i> Attendance
                                            </a>
                                            <a href="{{ route('parent.children.results', $child) }}" class="btn btn-outline-success btn-sm flex-grow-1">
                                                <i class="fas fa-chart-line me-1"></i> Results
                                            </a>
                                            <a href="{{ route('parent.children.fees', $child) }}" class="btn btn-outline-warning btn-sm flex-grow-1">
                                                <i class="fas fa-rupee-sign me-1"></i> Fees
                                            </a>
                                        </div>
                                        
                                        <div class="mt-3 text-center">
                                            <a href="{{ route('parent.children.show', $child) }}" class="btn btn-primary btn-sm w-100">
                                                View Full Profile <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-child fa-4x text-muted mb-3"></i>
                            <h5>No Children Registered</h5>
                            <p class="text-muted">You don't have any children registered in the system.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .child-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        transition: all 0.3s;
        border: 1px solid var(--border);
        cursor: pointer;
        height: 100%;
    }
    
    .child-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        border-color: var(--primary);
    }
    
    .child-avatar-lg {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary);
    }
</style>
@endpush