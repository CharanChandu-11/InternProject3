{{-- resources/views/teacher/leaves/create.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Apply for Leave')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus me-2"></i> Apply for Leave
                    <a href="{{ route('teacher.leaves.index') }}" class="float-end btn btn-sm btn-outline-secondary">Back</a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Your available leave balance: <strong>{{ $leaveBalance['remaining'] }}</strong> days
                    </div>
                    
                    <form action="{{ route('teacher.leaves.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" required>
                                <option value="">Select Leave Type</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->days_allowed }} days/year)</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="5" required placeholder="Please provide detailed reason for leave..."></textarea>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i> Submit Application
                            </button>
                            <a href="{{ route('teacher.leaves.index') }}" class="btn btn-secondary btn-lg px-5 ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Calculate days when dates change
    function calculateDays() {
        const start = document.querySelector('input[name="start_date"]').value;
        const end = document.querySelector('input[name="end_date"]').value;
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('daysCount').textContent = diffDays;
        }
    }
    
    document.querySelector('input[name="start_date"]').addEventListener('change', calculateDays);
    document.querySelector('input[name="end_date"]').addEventListener('change', calculateDays);
</script>
@endpush