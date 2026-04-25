{{-- resources/views/student/hostel.blade.php --}}
@extends('layouts.student')

@section('title', 'Hostel')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-hotel"></i> My Hostel Details
                </div>
                <div class="card-body">
                    @if($allocation)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Hostel Information</h6>
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Hostel Name</span>
                                            <span class="fw-bold">{{ $allocation->room->hostel->name }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Hostel Type</span>
                                            <span class="fw-bold">{{ ucfirst(str_replace('_', ' ', $allocation->room->hostel->type)) }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Warden Name</span>
                                            <span class="fw-bold">{{ $allocation->room->hostel->warden_name }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="text-muted" style="width: 100px;">Warden Contact</span>
                                            <span class="fw-bold">{{ $allocation->room->hostel->warden_phone }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Room Information</h6>
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Room Number</span>
                                            <span class="fw-bold">{{ $allocation->room->room_number }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Room Type</span>
                                            <span class="fw-bold">{{ ucfirst($allocation->room->room_type) }}</span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="text-muted" style="width: 100px;">Allocation Date</span>
                                            <span class="fw-bold">{{ $allocation->allocation_date->format('d M, Y') }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="text-muted" style="width: 100px;">Monthly Fee</span>
                                            <span class="fw-bold">₹{{ number_format($allocation->room->fee_per_month, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            For any hostel-related issues, please contact the warden during office hours (9:00 AM - 5:00 PM).
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-hotel fa-4x text-muted mb-3"></i>
                            <h5>No Hostel Accommodation</h5>
                            <p class="text-muted">You haven't been allocated any hostel room yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection