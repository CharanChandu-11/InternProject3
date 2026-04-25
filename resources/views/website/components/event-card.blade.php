<div class="event-card">
    <div class="event-date">
        <span class="day">{{ $event->start_date->format('d') }}</span>
        <span class="month">{{ $event->start_date->format('M') }}</span>
    </div>
    <div class="event-details">
        <h5>{{ $event->title }}</h5>
        <p class="event-meta">
            <i class="fas fa-clock"></i> {{ $event->start_time?->format('h:i A') ?? 'All Day' }}
            <i class="fas fa-map-marker-alt ms-3"></i> {{ $event->venue }}
        </p>
        <p class="event-description">{{ Str::limit($event->description, 100) }}</p>
        <a href="#" class="btn-event">View Details <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

@push('styles')
<style>
    .event-card {
        display: flex;
        background: white;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .event-card:hover {
        transform: translateX(10px);
    }
    .event-date {
        text-align: center;
        min-width: 70px;
        margin-right: 20px;
        background: #007bff;
        color: white;
        padding: 10px;
        border-radius: 8px;
    }
    .event-date .day {
        font-size: 28px;
        font-weight: bold;
        display: block;
        line-height: 1;
    }
    .event-date .month {
        font-size: 16px;
        text-transform: uppercase;
    }
    .event-details h5 {
        margin-bottom: 8px;
        font-size: 18px;
        color: #333;
    }
    .event-meta {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 8px;
    }
    .event-description {
        color: #6c757d;
        margin-bottom: 10px;
        font-size: 14px;
    }
    .btn-event {
        color: #007bff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }
    .btn-event:hover {
        text-decoration: underline;
    }
</style>
@endpush