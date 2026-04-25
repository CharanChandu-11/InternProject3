{{-- resources/views/parent/child-attendance.blade.php --}}
@extends('layouts.parent')

@section('title', $student->user->name . ' - Attendance')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calendar-check me-2"></i> 
                            Attendance - {{ $student->user->name }}
                        </div>
                        <div>
                            <form method="GET" class="d-inline">
                                <select name="month" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                                <select name="year" class="form-select form-select-sm d-inline-block w-auto ms-2" onchange="this.form.submit()">
                                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </form>
                            <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $totalDays }}</h3>
                                    <small>Total Days</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $presentDays }}</h3>
                                    <small>Present</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $absentDays }}</h3>
                                    <small>Absent</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">{{ $lateDays }}</h3>
                                    <small>Late</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Percentage -->
                    <div class="text-center mb-4">
                        <div class="display-4 fw-bold text-{{ $percentage >= 75 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger') }}">
                            {{ $percentage }}%
                        </div>
                        <p class="text-muted">Attendance Percentage</p>
                        <div class="progress mx-auto" style="height: 10px; max-width: 300px;">
                            <div class="progress-bar bg-{{ $percentage >= 75 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger') }}" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Calendar View -->
                    <h6 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> Daily Attendance</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Sun</th>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstDay = \Carbon\Carbon::createFromDate($year, $month, 1);
                                    $startDay = $firstDay->copy()->startOfWeek();
                                    $endDay = $firstDay->copy()->endOfMonth()->endOfWeek();
                                    $currentDay = $startDay->copy();
                                @endphp
                                @while($currentDay <= $endDay)
                                    <tr>
                                        @for($i = 0; $i < 7; $i++)
                                            @php
                                                $attendance = collect($calendar)->firstWhere('date', $currentDay->toDateString());
                                                $isCurrentMonth = $currentDay->month == $month;
                                            @endphp
                                            <td class="{{ !$isCurrentMonth ? 'bg-light text-muted' : '' }}" style="padding: 10px;">
                                                <div>{{ $currentDay->day }}</div>
                                                @if($isCurrentMonth && $attendance)
                                                    @if($attendance['status'] == 'present')
                                                        <i class="fas fa-check-circle text-success fa-lg mt-1"></i>
                                                    @elseif($attendance['status'] == 'absent')
                                                        <i class="fas fa-times-circle text-danger fa-lg mt-1"></i>
                                                    @elseif($attendance['status'] == 'late')
                                                        <i class="fas fa-clock text-warning fa-lg mt-1"></i>
                                                    @else
                                                        <i class="fas fa-minus-circle text-secondary fa-lg mt-1"></i>
                                                    @endif
                                                @elseif($isCurrentMonth)
                                                    <i class="fas fa-question-circle text-muted fa-lg mt-1"></i>
                                                @endif
                                            </td>
                                            @php $currentDay->addDay(); @endphp
                                        @endfor
                                    </tr>
                                @endwhile
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Legend -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <div><i class="fas fa-check-circle text-success"></i> Present</div>
                        <div><i class="fas fa-times-circle text-danger"></i> Absent</div>
                        <div><i class="fas fa-clock text-warning"></i> Late</div>
                        <div><i class="fas fa-minus-circle text-secondary"></i> Not Marked</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection