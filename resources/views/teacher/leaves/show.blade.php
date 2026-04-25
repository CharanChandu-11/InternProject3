{{-- resources/views/teacher/leaves/show.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Leave Application Details')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2"></i> Leave Application Details
                    <a href="{{ route('teacher.leaves.index') }}" class="float-end btn btn-sm btn-outline-secondary">Back</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">Leave Type</small>
                                <h5 class="mb-0">{{ $leave->leaveType->name }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">Status</small>
                                <div>
                                    @if($leave->status == 'pending')
                                        <span class="badge bg-warning fs-6">Pending Approval</span>
                                    @elseif($leave->status == 'approved')
                                        <span class="badge bg-success fs-6">Approved</span>
                                    @elseif($leave->status == 'rejected')
                                        <span class="badge bg-danger fs-6">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Cancelled</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">Start Date</small>
                                <h5 class="mb-0">{{ $leave->start_date->format('l, F j, Y') }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">End Date</small>
                                <h5 class="mb-0">{{ $leave->end_date->format('l, F j, Y') }}</h5>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">Total Days</small>
                                <h5 class="mb-0">{{ $leave->total_days }} day(s)</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">Applied On</small>
                                <h5 class="mb-0">{{ $leave->created_at->format('l, F j, Y h:i A') }}</h5>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Reason</small>
                            <p class="mb-0 mt-2">{{ $leave->reason }}</p>
                        </div>
                    </div>
                    
                    @if($leave->remarks)
                        <div class="mb-4">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">Remarks</small>
                                <p class="mb-0 mt-2">{{ $leave->remarks }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($leave->approved_by)
                        <div class="mb-4">
                            <div class="bg-light rounded-3 p-3">
                                <small class="text-muted">Approved By</small>
                                <p class="mb-0 mt-2">{{ $leave->approvedBy->name }} on {{ $leave->approved_date->format('d M, Y') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection