{{-- resources/views/admin/exam-results/bulk.blade.php --}}
@extends('layouts.admin')

@section('title', 'Bulk Marks Entry')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Bulk Marks Entry
            <div class="float-end">
                <span class="badge bg-primary">{{ $examSchedule->exam->name }}</span>
                <span class="badge bg-info">{{ $examSchedule->subject->name }}</span>
                <span class="badge bg-success">{{ $examSchedule->class->name }} - {{ $examSchedule->section->name }}</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.exam-results.bulk-store', $examSchedule) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Theory Marks ({{ $examSchedule->total_marks }})</th>
                                @if($examSchedule->practical_marks)
                                    <th>Practical Marks ({{ $examSchedule->practical_marks }})</th>
                                @endif
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            @php
                                $result = $existingResults[$student->id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $student->roll_number }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $student->user->profile_photo_url }}" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                                        <span>{{ $student->user->name }}</span>
                                    </div>
                                    <input type="hidden" name="marks[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                                </td>
                                <td>
                                    <input type="number" name="marks[{{ $loop->index }}][theory_marks]" class="form-control theory-marks" 
                                           value="{{ $result?->theory_marks_obtained }}" 
                                           min="0" max="{{ $examSchedule->total_marks }}" 
                                           data-student="{{ $student->id }}">
                                </td>
                                @if($examSchedule->practical_marks)
                                <td>
                                    <input type="number" name="marks[{{ $loop->index }}][practical_marks]" class="form-control practical-marks" 
                                           value="{{ $result?->practical_marks_obtained }}" 
                                           min="0" max="{{ $examSchedule->practical_marks }}">
                                </td>
                                @endif
                                <td>
                                    <input type="text" name="marks[{{ $loop->index }}][remarks]" class="form-control" value="{{ $result?->remarks }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save All Marks</button>
                    <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Optional: Auto-calculate total marks
    $('.theory-marks, .practical-marks').on('input', function() {
        // You can add logic to calculate total if needed
    });
</script>
@endpush
@endsection