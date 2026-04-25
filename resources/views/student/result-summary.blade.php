{{-- resources/views/student/result-summary.blade.php --}}
@extends('layouts.student')

@section('title', 'Result Summary')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i> Performance Summary
                <a href="{{ route('student.results') }}" class="btn btn-sm btn-secondary float-end">View All Results</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4 mx-auto">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h2>{{ $overallAverage }}%</h2>
                                <p>Overall Average</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5>Subject-wise Performance</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                             <tr>
                                <th>Subject</th>
                                <th>Average</th>
                                <th>Best Score</th>
                                <th>Worst Score</th>
                                <th>Trend</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectPerformance as $subject)
                                 <tr>
                                    <td>{{ $subject['subject'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">{{ $subject['average'] }}%</span>
                                            <div class="progress flex-grow-1" style="height: 5px;">
                                                <div class="progress-bar bg-{{ $subject['average'] >= 60 ? 'success' : ($subject['average'] >= 40 ? 'warning' : 'danger') }}" 
                                                     style="width: {{ $subject['average'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $subject['best'] }}%</td>
                                    <td>{{ $subject['worst'] }}%</td>
                                    <td>
                                        @if($subject['average'] >= 75)
                                            <i class="fas fa-arrow-up text-success"></i> Excellent
                                        @elseif($subject['average'] >= 60)
                                            <i class="fas fa-chart-line text-info"></i> Good
                                        @elseif($subject['average'] >= 40)
                                            <i class="fas fa-minus text-warning"></i> Average
                                        @else
                                            <i class="fas fa-arrow-down text-danger"></i> Needs Improvement
                                        @endif
                                    </td>
                                 </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <canvas id="subjectChart" height="300" class="mt-4"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('subjectChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($subjectPerformance)->pluck('subject')) !!},
            datasets: [{
                label: 'Average Percentage',
                data: {!! json_encode(collect($subjectPerformance)->pluck('average')) !!},
                backgroundColor: 'rgba(78, 115, 223, 0.6)',
                borderColor: '#4e73df',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentage (%)'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + '%';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush