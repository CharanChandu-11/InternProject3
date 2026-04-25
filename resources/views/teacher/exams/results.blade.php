{{-- resources/views/teacher/exams/results.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Exam Results')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-bar me-2"></i> Exam Results
            <div class="float-end">
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-list me-1"></i> All Exams
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="exam_id" class="form-select">
                            <option value="">All Exams</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="class_id" class="form-select" id="classSelect">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="section_id" class="form-select" id="sectionSelect">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('teacher.exams.results') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>
            
            <!-- Results Table -->
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Theory Marks</th>
                            <th>Practical Marks</th>
                            <th>Total</th>
                            <th>Max Marks</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                        <tr>
                            <td>
                                <img src="{{ $result->student->user->profile_photo_url }}" alt="" 
                                     style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;" class="me-2">
                                {{ $result->student->user->name }}<br>
                                <small class="text-muted">{{ $result->student->admission_number }}</small>
                            </td>
                            <td>{{ $result->examSchedule->exam->name }}<br>
                                <small>{{ $result->examSchedule->exam->examType->name ?? '' }}</small>
                            </td>
                            <td>{{ $result->examSchedule->subject->name }}<br>
                                <small>({{ $result->examSchedule->subject->code }})</small>
                            </td>
                            <td>{{ $result->theory_marks_obtained ?? '-' }} / {{ $result->examSchedule->total_marks ?? 0 }}</span></td>
                            <td>{{ $result->practical_marks_obtained ?? '-' }} / {{ $result->examSchedule->practical_marks ?? 0 }}</span></td>
                            <td><strong>{{ $result->total_marks_obtained }}</strong></td>
                            <td>{{ ($result->examSchedule->total_marks ?? 0) + ($result->examSchedule->practical_marks ?? 0) }}</span></td>
                            <td>{{ $result->percentage }}%</span></td>
                            <td>
                                <span class="badge bg-{{ $result->grade == 'F' ? 'danger' : ($result->grade == 'A+' ? 'success' : 'info') }}">
                                    {{ $result->grade }}
                                </span>
                            </td>
                            <td>{{ $result->remarks ?? '-' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>
            
            {{ $results->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#classSelect').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/teacher/exams/sections/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionSelect');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionSelect').empty().append('<option value="">All Sections</option>');
        }
    });
</script>
@endpush