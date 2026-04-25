@extends('website.layouts.app')

@section('title', 'Events - Smart School ERP')

@section('content')
    <section class="page-header">
        <div class="container">
            <h1>Upcoming Events</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('website.home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Events</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="events-content py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2>Upcoming Events</h2>
                    @forelse($upcomingEvents as $event)
                        <div class="event-card-large mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="event-date-large">
                                        <span class="day">{{ $event->start_date->format('d') }}</span>
                                        <span class="month">{{ $event->start_date->format('M') }}</span>
                                        <span class="year">{{ $event->start_date->format('Y') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <h4>{{ $event->title }}</h4>
                                    <p class="event-meta">
                                        <i class="fas fa-clock"></i> {{ $event->start_time?->format('h:i A') ?? 'All Day' }}
                                        <i class="fas fa-map-marker-alt ms-3"></i> {{ $event->venue }}
                                    </p>
                                    <p>{{ Str::limit($event->description, 200) }}</p>
                                    <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No upcoming events at the moment.</p>
                    @endforelse

                    <hr class="my-5">

                    <h2>Past Events</h2>
                    @forelse($pastEvents as $event)
                        <div class="event-card-small mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="event-date-small">
                                        {{ $event->start_date->format('M d, Y') }}
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <h5>{{ $event->title }}</h5>
                                    <p class="text-muted">{{ $event->venue }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No past events found.</p>
                    @endforelse

                    <div class="d-flex justify-content-center mt-4">
                        {{ $pastEvents->links() }}
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sidebar-widget">
                        <h4>Event Calendar</h4>
                        <div id="calendar"></div>
                    </div>
                    <div class="sidebar-widget">
                        <h4>Subscribe to Updates</h4>
                        <form class="newsletter-form">
                            @csrf
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                                <button class="btn btn-primary" type="submit">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    .event-card-large {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .event-card-large:hover {
        transform: translateY(-3px);
    }
    .event-date-large {
        text-align: center;
        background: #007bff;
        color: white;
        padding: 15px;
        border-radius: 8px;
    }
    .event-date-large .day {
        font-size: 36px;
        font-weight: bold;
        display: block;
        line-height: 1;
    }
    .event-date-large .month {
        font-size: 18px;
        text-transform: uppercase;
    }
    .event-date-large .year {
        font-size: 14px;
    }
    .event-card-small {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        transition: background 0.3s;
    }
    .event-card-small:hover {
        background: #e9ecef;
    }
    .event-date-small {
        font-weight: bold;
        color: #007bff;
    }
    .sidebar-widget {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
    }
    .sidebar-widget h4 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },
            events: [
                @foreach($upcomingEvents as $event)
                {
                    title: '{{ $event->title }}',
                    start: '{{ $event->start_date->format('Y-m-d') }}',
                    url: '#'
                },
                @endforeach
                @foreach($pastEvents as $event)
                {
                    title: '{{ $event->title }}',
                    start: '{{ $event->start_date->format('Y-m-d') }}',
                    url: '#'
                },
                @endforeach
            ]
        });
        calendar.render();
    });
</script>
@endpush