{{-- resources/views/student/hostel/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Hostel Management')

@section('content')
<div class="animate-fadeInUp">
    @if($allocation && $allocation->status == 'active')
        <!-- Active Hostel Allocation -->
        <div class="row">
            <!-- Hostel Information Card -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-hotel me-2"></i> Hostel Information
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="hostel-icon mb-3">
                                <i class="fas fa-building fa-5x text-primary"></i>
                            </div>
                            <h3 class="mb-1">{{ $allocation->room->hostel->name }}</h3>
                            <p class="text-muted">
                                <span class="badge bg-info">{{ ucfirst($allocation->room->hostel->type) }} Hostel</span>
                                <span class="badge bg-{{ $allocation->status_color }} ms-2">{{ $allocation->status_text }}</span>
                            </p>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <label><i class="fas fa-door-open text-primary me-2"></i> Room Number</label>
                                <p class="mb-0 fw-bold">{{ $allocation->room->room_number }}</p>
                            </div>
                            <div class="info-item">
                                <label><i class="fas fa-bed text-primary me-2"></i> Room Type</label>
                                <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $allocation->room->room_type)) }}</p>
                            </div>
                            <div class="info-item">
                                <label><i class="fas fa-users text-primary me-2"></i> Capacity</label>
                                <p class="mb-0">{{ $allocation->room->capacity }} persons</p>
                            </div>
                            <div class="info-item">
                                <label><i class="fas fa-rupee-sign text-primary me-2"></i> Fee per Month</label>
                                <p class="mb-0 fw-bold text-success">₹ {{ number_format($monthlyFee, 2) }}</p>
                            </div>
                            <div class="info-item">
                                <label><i class="fas fa-calendar-alt text-primary me-2"></i> Allocated On</label>
                                <p class="mb-0">{{ $allocation->allocation_date->format('d M, Y') }}</p>
                            </div>
                            <div class="info-item">
                                <label><i class="fas fa-calendar-times text-primary me-2"></i> Leave Date</label>
                                <p class="mb-0">{{ $allocation->leave_date ? $allocation->leave_date->format('d M, Y') : 'Not specified' }}</p>
                            </div>
                        </div>
                        
                        @if($daysRemaining && $daysRemaining > 0)
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-clock me-2"></i>
                                <strong>{{ $daysRemaining }}</strong> days remaining in current allocation
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Warden Information Card -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-user-shield me-2"></i> Warden Information
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="warden-avatar mb-3">
                                <i class="fas fa-user-circle fa-5x text-info"></i>
                            </div>
                            <h4 class="mb-1">{{ $allocation->room->hostel->warden_name ?? 'Not Assigned' }}</h4>
                            <p class="text-muted">Hostel Warden</p>
                        </div>
                        
                        @if($allocation->room->hostel->warden_phone)
                            <div class="contact-box">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-phone-alt fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <label class="text-muted mb-0">Contact Number</label>
                                        <p class="mb-0 fw-bold">{{ $allocation->room->hostel->warden_phone }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($allocation->room->hostel->address)
                            <div class="contact-box">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="fas fa-map-marker-alt fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <label class="text-muted mb-0">Address</label>
                                        <p class="mb-0">{{ $allocation->room->hostel->address }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Roommates Card -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-users me-2"></i> Roommates
                        <span class="float-end badge bg-primary">{{ $roommates->count() }} / {{ $allocation->room->capacity - 1 }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($roommates->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($roommates as $roommate)
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $roommate->student->user->profile_photo_url }}" alt="Photo" 
                                                 class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $roommate->student->user->name }}</h6>
                                                <small class="text-muted">
                                                    Admission: {{ $roommate->student->admission_number }} | 
                                                    Class: {{ $roommate->student->class->name ?? 'N/A' }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">Allocated: {{ $roommate->allocation_date->format('d M Y') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-user-friends fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No roommates yet. You have the room to yourself!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Fee Status Card -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-rupee-sign me-2"></i> Fee Status
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="fee-card">
                                    <h6 class="text-muted mb-2">Monthly Fee</h6>
                                    <h3 class="text-primary mb-0">₹ {{ number_format($monthlyFee, 2) }}</h3>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="fee-card">
                                    <h6 class="text-muted mb-2">Total Paid</h6>
                                    <h3 class="text-success mb-0">₹ {{ number_format($totalPaid, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Payment Progress</span>
                                <span class="fw-bold">{{ $totalPaid > 0 ? round(($totalPaid / $monthlyFee) * 100, 2) : 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ $totalPaid > 0 ? ($totalPaid / $monthlyFee) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        
                        @if($totalDue > 0)
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Due Amount:</strong> ₹ {{ number_format($totalDue, 2) }}
                                <br>
                                <small>Please pay your hostel fee on time to avoid penalties.</small>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Fully Paid!</strong> Your hostel fee is up to date.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Hostel Amenities Card -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-check-circle me-2"></i> Hostel Amenities
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($amenities as $amenity)
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i> {{ $amenity }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities Card -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-history me-2"></i> Recent Activities
                    </div>
                    <div class="card-body p-0">
                        <div class="timeline">
                            @foreach($recentActivities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">{{ $activity['description'] }}</span>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($activity['date'])->format('d M, Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hostel Rules Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning">
                <i class="fas fa-gavel me-2"></i> Hostel Rules & Regulations
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Maintain silence in the hostel premises after 10:00 PM</li>
                            <li>Visitors are not allowed inside rooms without warden's permission</li>
                            <li>Report any maintenance issues to the warden immediately</li>
                            <li>Keep your room clean and tidy</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Follow the hostel dress code</li>
                            <li>No smoking or alcohol consumption inside hostel</li>
                            <li>Be respectful to fellow residents and staff</li>
                            <li>Inform warden before leaving for holidays</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
    @elseif($allocation && $allocation->status == 'pending')
        <!-- Pending Allocation -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="pending-icon mb-4">
                    <i class="fas fa-hourglass-half fa-5x text-warning"></i>
                </div>
                <h3>Allocation Request Pending</h3>
                <p class="text-muted">Your hostel allocation request is awaiting admin approval.</p>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Request Details:</strong>
                    <br>
                    Hostel: {{ $allocation->room->hostel->name }}<br>
                    Room: {{ $allocation->room->room_number }} ({{ ucfirst(str_replace('_', ' ', $allocation->room->room_type)) }})<br>
                    Requested on: {{ $allocation->created_at->format('d M, Y h:i A') }}
                </div>
                <p class="text-muted mt-3">You will be notified once your request is processed.</p>
            </div>
        </div>
        
    @else
        <!-- No Hostel Allocation - Show Available Hostels -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="no-hostel-icon mb-4">
                    <i class="fas fa-hotel fa-5x text-muted"></i>
                </div>
                <h3>No Hostel Allocation</h3>
                <p class="text-muted">You don't have any active hostel allocation.</p>
                <hr>
                <h5 class="mt-4">Available Hostels</h5>
            </div>
        </div>
        
        @if($hostels->count() > 0)
            <div class="row mt-3">
                @foreach($hostels as $hostel)
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm h-100 hostel-card">
                            <div class="card-body text-center">
                                <div class="hostel-icon mb-3">
                                    <i class="fas fa-building fa-4x text-primary"></i>
                                </div>
                                <h5 class="card-title">{{ $hostel->name }}</h5>
                                <p class="text-muted">
                                    <span class="badge bg-info">{{ ucfirst($hostel->type) }}</span>
                                </p>
                                <div class="hostel-stats mb-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Total Rooms</small>
                                            <h6 class="mb-0">{{ $hostel->rooms->count() }}</h6>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Available</small>
                                            <h6 class="mb-0 text-success">
                                                {{ $hostel->rooms->sum(function($room) { return $room->capacity - $room->occupied; }) }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                @if($hostel->warden_name)
                                    <p class="small text-muted mb-2">
                                        <i class="fas fa-user-shield me-1"></i> Warden: {{ $hostel->warden_name }}
                                    </p>
                                @endif
                                <a href="{{ route('student.hostel.available-rooms', $hostel) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i> View Available Rooms
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hostels available at the moment.</p>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection

@push('styles')
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .info-item label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 5px;
        display: block;
    }
    
    .info-item p {
        font-size: 15px;
        margin-bottom: 0;
    }
    
    .contact-box {
        padding: 12px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 15px;
    }
    
    .contact-icon {
        width: 50px;
        height: 50px;
        background: rgba(23, 162, 184, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .fee-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
    }
    
    .timeline {
        position: relative;
        padding: 20px;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 30px;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #007bff;
    }
    
    .timeline-item:not(:last-child) .timeline-marker::after {
        content: '';
        position: absolute;
        left: 4px;
        top: 12px;
        width: 2px;
        height: calc(100% + 8px);
        background: #dee2e6;
    }
    
    .timeline-content {
        padding-bottom: 5px;
    }
    
    .hostel-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .hostel-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    
    .hostel-stats {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 10px;
    }
    
    .pending-icon, .no-hostel-icon {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Add any interactive features if needed
    $(document).ready(function() {
        // Animate progress bars on load
        $('.progress-bar').each(function() {
            var width = $(this).css('width');
            $(this).css('width', '0');
            setTimeout(() => {
                $(this).css('width', width);
            }, 100);
        });
    });
</script>
@endpush