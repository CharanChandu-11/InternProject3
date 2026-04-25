{{-- resources/views/teacher/parent-leave-requests/show.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Leave Request Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-umbrella-beach me-2"></i> Leave Request Details
            <div class="float-end">
                <a href="{{ route('teacher.parent-leave-requests.index') }}" class="btn btn-sm btn-secondary">
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
                            {{ $leaveRequest->student->user->name }}<br>
                            <small>Class: {{ $leaveRequest->student->class->name }} - Section {{ $leaveRequest->student->section->name }}</small>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h6><i class="fas fa-user me-2 text-primary"></i> Parent</h6>
                        <p>{{ $leaveRequest->parent->name }}<br>
                        <small>{{ $leaveRequest->parent->email }} | {{ $leaveRequest->parent->phone }}</small></p>
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
                <div class="col-md-12">
                    <div class="info-box">
                        <h6><i class="fas fa-align-left me-2 text-primary"></i> Reason</h6>
                        <p>{{ $leaveRequest->reason }}</p>
                    </div>
                </div>
                @if($leaveRequest->remarks)
                <div class="col-md-12">
                    <div class="info-box">
                        <h6><i class="fas fa-comment me-2 text-primary"></i> Parent Remarks</h6>
                        <p>{{ $leaveRequest->remarks }}</p>
                    </div>
                </div>
                @endif
                @if($leaveRequest->attachment)
                <div class="col-md-12">
                    <div class="info-box">
                        <h6><i class="fas fa-paperclip me-2 text-primary"></i> Attachment</h6>
                        <p>
                            <a href="{{ Storage::url($leaveRequest->attachment) }}" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-download me-1"></i> Download Attachment
                            </a>
                        </p>
                    </div>
                </div>
                @endif
            </div>
            
            @if($leaveRequest->status == 'pending')
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-check-circle me-2"></i> Teacher Action Required
                            </div>
                            <div class="card-body">
                                <form action="{{ route('teacher.parent-leave-requests.approve', $leaveRequest) }}" method="POST" class="d-inline">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Teacher Remarks (Optional)</label>
                                        <textarea name="teacher_remarks" class="form-control" rows="2" placeholder="Add any remarks..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i> Approve Leave
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="fas fa-times me-1"></i> Reject Leave
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($leaveRequest->status == 'approved_by_teacher')
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Teacher Approved:</strong> This leave request has been approved by the teacher and is waiting for admin approval.
                    @if($leaveRequest->teacher_remarks)
                        <br><strong>Teacher Remarks:</strong> {{ $leaveRequest->teacher_remarks }}
                    @endif
                </div>
            @elseif($leaveRequest->status == 'rejected')
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Leave Request Rejected</strong>
                    @if($leaveRequest->rejection_reason)
                        <br><strong>Reason:</strong> {{ $leaveRequest->rejection_reason }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teacher.parent-leave-requests.reject', $leaveRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
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