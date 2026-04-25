{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="animate-fadeInUp">
    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Total Students</h6>
                            <h2 class="mb-0">{{ $totalStudents ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-graduation-cap fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Total Teachers</h6>
                            <h2 class="mb-0">{{ $totalTeachers ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-chalkboard-user fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Announcements</h6>
                            <h2 class="mb-0">{{ $totalAnnouncements ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-bullhorn fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Upcoming Events</h6>
                            <h2 class="mb-0">{{ $upcomingEvents ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Announcements -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-bullhorn me-2"></i> Recent Announcements
                    <a href="{{ route('admin.announcements.create') }}" class="btn btn-sm btn-primary float-end">
                        <i class="fas fa-plus me-1"></i> New
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentAnnouncements ?? [] as $announcement)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ Str::limit($announcement->title, 60) }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i> {{ $announcement->publish_date->format('M d, Y') }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-users me-1"></i> {{ ucfirst($announcement->audience) }}
                                        </small>
                                    </div>
                                    <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                No announcements yet
                            </div>
                        @endforelse
                    </div>
                </div>
                @if(isset($recentAnnouncements) && $recentAnnouncements->count() > 0)
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.announcements.index') }}" class="text-decoration-none">
                            View All Announcements <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Upcoming Events
                    <a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-primary float-end">
                        <i class="fas fa-plus me-1"></i> Add Event
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($upcomingEventsList ?? [] as $event)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ Str::limit($event->title, 50) }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i> {{ $event->start_date->format('M d, Y') }}
                                            @if($event->start_time)
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-clock me-1"></i> {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                            @endif
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-map-marker-alt me-1"></i> {{ Str::limit($event->venue, 30) }}
                                        </small>
                                    </div>
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                No upcoming events
                            </div>
                        @endforelse
                    </div>
                </div>
                @if(isset($upcomingEventsList) && $upcomingEventsList->count() > 0)
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.events.index') }}" class="text-decoration-none">
                            View All Events <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Announcement Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <i class="fas fa-chart-pie me-2"></i> Announcement Stats
                </div>
                <div class="card-body">
                    <canvas id="announcementStatsChart" height="200"></canvas>
                    <div class="mt-3 text-center">
                        <div class="row">
                            <div class="col-4">
                                <span class="badge bg-success">Published</span>
                                <h4>{{ $publishedCount ?? 0 }}</h4>
                            </div>
                            <div class="col-4">
                                <span class="badge bg-warning">Scheduled</span>
                                <h4>{{ $scheduledCount ?? 0 }}</h4>
                            </div>
                            <div class="col-4">
                                <span class="badge bg-secondary">Draft</span>
                                <h4>{{ $draftCount ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <i class="fas fa-chart-line me-2"></i> Event Stats
                </div>
                <div class="card-body">
                    <canvas id="eventStatsChart" height="200"></canvas>
                    <div class="mt-3 text-center">
                        <div class="row">
                            <div class="col-4">
                                <span class="badge bg-info">Upcoming</span>
                                <h4>{{ $upcomingEvents ?? 0 }}</h4>
                            </div>
                            <div class="col-4">
                                <span class="badge bg-success">Ongoing</span>
                                <h4>{{ $ongoingEvents ?? 0 }}</h4>
                            </div>
                            <div class="col-4">
                                <span class="badge bg-secondary">Completed</span>
                                <h4>{{ $completedEvents ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <i class="fas fa-bolt me-2"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Create Announcement
                        </a>
                        <a href="{{ route('admin.events.create') }}" class="btn btn-success">
                            <i class="fas fa-calendar-plus me-2"></i> Add New Event
                        </a>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-info">
                            <i class="fas fa-bullhorn me-2"></i> Manage Announcements
                        </a>
                        <a href="{{ route('admin.events.index') }}" class="btn btn-warning">
                            <i class="fas fa-calendar-alt me-2"></i> Manage Events
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Announcement Stats Chart
    var ctx1 = document.getElementById('announcementStatsChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Published', 'Scheduled', 'Draft'],
            datasets: [{
                data: [{{ $publishedCount ?? 0 }}, {{ $scheduledCount ?? 0 }}, {{ $draftCount ?? 0 }}],
                backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Event Stats Chart
    var ctx2 = document.getElementById('eventStatsChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Upcoming', 'Ongoing', 'Completed'],
            datasets: [{
                data: [{{ $upcomingEvents ?? 0 }}, {{ $ongoingEvents ?? 0 }}, {{ $completedEvents ?? 0 }}],
                backgroundColor: ['#17a2b8', '#28a745', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush