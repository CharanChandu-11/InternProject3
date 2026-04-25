{{-- resources/views/parent/leave-requests/show.blade.php --}}
@extends('layouts.parent')

@section('title', 'Leave Request Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-umbrella-beach me-2"></i> Leave Request Details
            <div class="float-end">
                <a href="{{ route('parent.leave-requests.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-user-graduate me-2 text-primary"></i> Student</h6>
                        <p>
                            <img src="{{ $leaveRequest->student->user->profile_photo_url }}" alt="" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" class="me-2">
                            {{ $leaveRequest->student->user->name }} ({{ $leaveRequest->student->admission_number }})
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-tag me-2 text-primary"></i> Leave Type</h6>
                        <p>{{ $leaveRequest->leaveType->name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-calendar-alt me-2 text-primary"></i> Duration</h6>
                        <p>{{ $leaveRequest->start_date->format('l, F j, Y') }} - {{ $leaveRequest->end_date->format('l, F j, Y') }}</p>
                        <p><strong>Total Days:</strong> {{ $leaveRequest->total_days }} days</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-info-circle me-2 text-primary"></i> Status</h6>
                        <p>
                            <span class="badge bg-{{ $leaveRequest->status_color }} fs-6">
                                {{ $leaveRequest->status_text }}
                            </span>
                            @if($leaveRequest->approved_at)
                                <br><small class="text-muted">Approved on: {{ $leaveRequest->approved_at->format('d M Y, h:i A') }}</small>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="info-box">
                        <h6><i class="fas fa-align-left me-2 text-primary"></i> Reason</h6>
                        <p>{{ $leaveRequest->reason }}</p>
                    </div>
                </div>
                @if($leaveRequest->remarks)
                <div class="col-md-12">
                    <div class="info-box">
                        <h6><i class="fas fa-comment me-2 text-primary"></i> Additional Remarks</h6>
                        <p>{{ $leaveRequest->remarks }}</p>
                    </div>
                </div>
                @endif
                @if($leaveRequest->rejection_reason)
                <div class="col-md-12">
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i> Rejection Reason</h6>
                        <p>{{ $leaveRequest->rejection_reason }}</p>
                    </div>
                </div>
                @endif
                @if($leaveRequest->attachment)
                <div class="col-md-12">
                    <div class="info-box">
                        <h6><i class="fas fa-paperclip me-2 text-primary"></i> Attachment</h6>
                        <p>
                            <a href="{{ route('parent.leave-requests.download', $leaveRequest) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-download me-1"></i> Download Attachment
                            </a>
                        </p>
                    </div>
                </div>
                @endif
            </div>
            
            @if($leaveRequest->status == 'pending')
                <div class="mt-3">
                    <a href="{{ route('parent.leave-requests.edit', $leaveRequest) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Request
                    </a>
                    <form action="{{ route('parent.leave-requests.cancel', $leaveRequest) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="button" class="btn btn-warning delete-btn">
                            <i class="fas fa-ban me-1"></i> Cancel Request
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 15px;
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