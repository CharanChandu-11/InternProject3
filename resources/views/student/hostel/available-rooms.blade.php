{{-- resources/views/student/hostel/available-rooms.blade.php --}}
@extends('layouts.student')

@section('title', 'Available Rooms - ' . $hostel->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-bed me-2"></i> Available Rooms in {{ $hostel->name }}
            <div class="float-end">
                <a href="{{ route('student.hostel') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Hostel Info -->
            <div class="alert alert-info mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-building me-2"></i> Hostel Type:</strong> {{ ucfirst($hostel->type) }}
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-user-shield me-2"></i> Warden:</strong> {{ $hostel->warden_name ?? 'Not Assigned' }}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <strong><i class="fas fa-map-marker-alt me-2"></i> Address:</strong> {{ $hostel->address ?? 'N/A' }}
                    </div>
                </div>
            </div>
            
            @if($rooms->count() > 0)
                <div class="row">
                    @foreach($rooms as $room)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-{{ $room['available_seats'] > 0 ? 'success' : 'danger' }} text-white">
                                    <i class="fas fa-door-open me-2"></i> Room {{ $room['room_number'] }}
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-bed fa-3x text-primary"></i>
                                    </div>
                                    
                                    <div class="info-row mb-2">
                                        <strong>Room Type:</strong>
                                        <span>{{ $room['room_type_text'] }}</span>
                                    </div>
                                    <div class="info-row mb-2">
                                        <strong>Capacity:</strong>
                                        <span>{{ $room['capacity'] }} persons</span>
                                    </div>
                                    <div class="info-row mb-2">
                                        <strong>Currently Occupied:</strong>
                                        <span>{{ $room['occupied'] }} persons</span>
                                    </div>
                                    <div class="info-row mb-2">
                                        <strong>Available Seats:</strong>
                                        <span class="text-{{ $room['available_seats'] > 0 ? 'success' : 'danger' }} fw-bold">
                                            {{ $room['available_seats'] }}
                                        </span>
                                    </div>
                                    <div class="info-row mb-3">
                                        <strong>Fee per Month:</strong>
                                        <span class="text-primary fw-bold">{{ $room['fee_per_month_formatted'] }}</span>
                                    </div>
                                    
                                    <div class="progress mb-3" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $room['occupied'] == $room['capacity'] ? 'danger' : 'success' }}" 
                                             style="width: {{ ($room['occupied'] / $room['capacity']) * 100 }}%"></div>
                                    </div>
                                    
                                    @if($room['available_seats'] > 0)
                                        <button type="button" class="btn btn-primary w-100 request-btn"
                                                data-room-id="{{ $room['id'] }}"
                                                data-room-number="{{ $room['room_number'] }}"
                                                data-room-type="{{ $room['room_type_text'] }}"
                                                data-fee="{{ $room['fee_per_month_formatted'] }}">
                                            <i class="fas fa-hand-holding-heart me-1"></i> Request Allocation
                                        </button>
                                    @else
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="fas fa-ban me-1"></i> Fully Occupied
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning text-center">
                    <i class="fas fa-info-circle me-2"></i> No rooms available in this hostel at the moment.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.hostel.request') }}" method="POST">
                @csrf
                <input type="hidden" name="room_id" id="roomId">
                <div class="modal-header">
                    <h5 class="modal-title">Request Hostel Allocation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="mb-1"><strong>Room Number:</strong> <span id="roomNumber"></span></p>
                        <p class="mb-1"><strong>Room Type:</strong> <span id="roomType"></span></p>
                        <p class="mb-0"><strong>Monthly Fee:</strong> <span id="roomFee"></span></p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Terms & Conditions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Allocation is subject to availability and approval</li>
                            <li>Hostel fee must be paid on time</li>
                            <li>Follow hostel rules and regulations</li>
                            <li>Maintain discipline and cleanliness</li>
                        </ul>
                    </div>
                    
                    <div class="form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="termsCheckbox" required>
                        <label class="form-check-label" for="termsCheckbox">
                            I agree to the terms and conditions
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-row {
        padding: 5px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('.request-btn').click(function() {
            var roomId = $(this).data('room-id');
            var roomNumber = $(this).data('room-number');
            var roomType = $(this).data('room-type');
            var roomFee = $(this).data('fee');
            
            $('#roomId').val(roomId);
            $('#roomNumber').text(roomNumber);
            $('#roomType').text(roomType);
            $('#roomFee').text(roomFee);
            
            $('#requestModal').modal('show');
        });
        
        $('#termsCheckbox').change(function() {
            $('#submitBtn').prop('disabled', !$(this).is(':checked'));
        });
    });
</script>
@endpush