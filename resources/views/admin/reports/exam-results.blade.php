{{-- resources/views/admin/reports/exam-results.blade.php --}}
@extends('layouts.admin')

@section('title', 'Exam Results Report')

@section('content')
<div class="animate-fadeInUp">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line me-2"></i> Exam Results Report
            <div class="float-end">
                <button class="btn btn-sm btn-success" id="exportExcel">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
                <button class="btn btn-sm btn-danger" id="exportPDF">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Exam</label>
                        <select name="exam_id" class="form-select">
                            <option value="">All Exams</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }} ({{ $exam->examType->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" id="sectionFilter">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <a href="{{ route('admin.reports.exam-results') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Results</h6>
                            <h3>{{ number_format($summary['total_results']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Students</h6>
                            <h3>{{ number_format($summary['total_students']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Average Percentage</h6>
                            <h3>{{ $summary['average_percentage'] }}%</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Pass Percentage</h6>
                            <h3>{{ $summary['pass_percentage'] }}%</h3>
                            <small>{{ $summary['pass_count'] }} Passed / {{ $summary['fail_count'] }} Failed</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-pie me-2"></i> Grade Distribution
                        </div>
                        <div class="card-body">
                            <canvas id="gradeChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar me-2"></i> Top Performers
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Student</th>
                                            <th>Percentage</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topPerformers as $index => $performer)
                                            <tr>
                                                <td>{{ $index + 1 }}</span></td>
                                                <td>
                                                    {{ $performer['student_name'] }}<br>
                                                    <small class="text-muted">{{ $performer['admission_number'] }}</small>
                                                 </span></td>
                                                <td>{{ $performer['percentage'] }}%</span></td>
                                                <td>{{ $performer['grade'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject-wise Performance -->
            @if(count($subjectPerformance) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-book me-2"></i> Subject-wise Performance
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Code</th>
                                    <th>Students</th>
                                    <th>Avg Marks</th>
                                    <th>Avg %</th>
                                    <th>Highest</th>
                                    <th>Lowest</th>
                                    <th>Pass</th>
                                    <th>Fail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectPerformance as $subject)
                                    <tr>
                                        <td>{{ $subject['subject'] }}</span></td>
                                        <td>{{ $subject['subject_code'] }}</span></td>
                                        <td>{{ number_format($subject['total_students']) }}</span></td>
                                        <td>{{ number_format($subject['average_marks'], 2) }}</span></td>
                                        <td>{{ number_format($subject['average_percentage'], 2) }}%</span></td>
                                        <td>{{ number_format($subject['highest'], 2) }}%</span></td>
                                        <td>{{ number_format($subject['lowest'], 2) }}%</span></td>
                                        <td><span class="text-success">{{ $subject['pass_count'] }}</span></span></td>
                                        <td><span class="text-danger">{{ $subject['fail_count'] }}</span></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Detailed Results -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> Detailed Results
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered datatable">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Exam</th>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Max Marks</th>
                                    <th>Percentage</th>
                                    <th>Grade</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td>
                                        <img src="{{ $result->student->user->profile_photo_url }}" alt="" 
                                             style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;" class="me-2">
                                        {{ $result->student->user->name }}
                                     </span></td>
                                    <td>{{ $result->student->class->name ?? 'N/A' }}</span></td>
                                    <td>{{ $result->examSchedule->exam->name ?? 'N/A' }}</span></td>
                                    <td>{{ $result->examSchedule->subject->name ?? 'N/A' }}</span></td>
                                    <td>{{ $result->total_marks_obtained }}</span></td>
                                    <td>{{ $result->examSchedule->total_marks + ($result->examSchedule->practical_marks ?? 0) }}</span></td>
                                    <td>{{ $result->percentage }}%</span></td>
                                    <td>{{ $result->grade }}</span></td>
                                    <td>
                                        <span class="badge bg-{{ $result->percentage >= 40 ? 'success' : 'danger' }}">
                                            {{ $result->percentage >= 40 ? 'Pass' : 'Fail' }}
                                        </span>
                                     </span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $results->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Grade Distribution Chart
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    const gradeLabels = @json(array_keys($gradeDistribution));
    const gradeCounts = @json(array_values($gradeDistribution));
    
    new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: gradeLabels,
            datasets: [{
                label: 'Number of Students',
                data: gradeCounts,
                backgroundColor: ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#17a2b8', '#6c757d', '#dc3545'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Number of Students' }
                }
            }
        }
    });
    
    // Dynamic section loading
    $('#classFilter').change(function() {
        var classId = $(this).val();
        if (classId) {
            $.ajax({
                url: '/admin/sections/by-class/' + classId,
                type: 'GET',
                success: function(data) {
                    var sectionSelect = $('#sectionFilter');
                    sectionSelect.empty();
                    sectionSelect.append('<option value="">All Sections</option>');
                    $.each(data, function(key, section) {
                        sectionSelect.append('<option value="' + section.id + '">' + section.name + '</option>');
                    });
                }
            });
        } else {
            $('#sectionFilter').empty().append('<option value="">All Sections</option>');
        }
    });
</script>
@endpush