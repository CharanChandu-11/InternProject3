{{-- resources/views/teacher/exams/mark.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Enter Marks - ' . $examSchedule->exam->name . ' - ' . $examSchedule->subject->name)

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> Enter Marks: {{ $examSchedule->exam->name }} - {{ $examSchedule->subject->name }}
            <div class="float-end">
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Students</h6>
                            <h3>{{ $stats['total_students'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Marks Entered</h6>
                            <h3>{{ $stats['marked_count'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Pending</h6>
                            <h3>{{ $stats['pending_count'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h6>Max Marks</h6>
                            <h3>{{ $stats['total_max'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('teacher.exams.save-marks', $examSchedule) }}" method="POST">
                @csrf
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Admission No</th>
                                @if($stats['theory_max'] > 0)
                                    <th>Theory Marks (Max: {{ $stats['theory_max'] }})</th>
                                @endif
                                @if($stats['practical_max'] > 0)
                                    <th>Practical Marks (Max: {{ $stats['practical_max'] }})</th>
                                @endif
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentsData as $index => $student)
                            <tr>
                                <td class="text-center">{{ $student['roll_number'] ?? '-' }}</td>
                                <td>
                                    <img src="{{ $student['profile_photo'] }}" alt="" 
                                         style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;" class="me-2">
                                    {{ $student['name'] }}
                                </td>
                                <td>{{ $student['admission_number'] }}</td>
                                <input type="hidden" name="marks[{{ $index }}][student_id]" value="{{ $student['id'] }}">
                                
                                @if($stats['theory_max'] > 0)
                                    <td>
                                        <input type="number" name="marks[{{ $index }}][theory_marks]" 
                                               class="form-control theory-marks" 
                                               value="{{ $student['theory_marks'] }}"
                                               min="0" max="{{ $stats['theory_max'] }}"
                                               onchange="calculateTotal(this, {{ $index }}, {{ $stats['practical_max'] > 0 ? 1 : 0 }})">
                                    </td>
                                @endif
                                
                                @if($stats['practical_max'] > 0)
                                    <td>
                                        <input type="number" name="marks[{{ $index }}][practical_marks]" 
                                               class="form-control practical-marks" 
                                               value="{{ $student['practical_marks'] }}"
                                               min="0" max="{{ $stats['practical_max'] }}"
                                               onchange="calculateTotal(this, {{ $index }}, 1)">
                                    </td>
                                @endif
                                
                                <td>
                                    <input type="text" name="marks[{{ $index }}][remarks]" 
                                           class="form-control" 
                                           value="{{ $student['remarks'] }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Marks
                    </button>
                    <a href="{{ route('teacher.exams.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function calculateTotal(element, index, hasPractical) {
        var theoryMarks = parseFloat($('.theory-marks').eq(index).val()) || 0;
        var practicalMarks = hasPractical ? (parseFloat($('.practical-marks').eq(index).val()) || 0) : 0;
        var total = theoryMarks + practicalMarks;
        console.log('Total for student ' + index + ': ' + total);
    }
</script>
@endpush