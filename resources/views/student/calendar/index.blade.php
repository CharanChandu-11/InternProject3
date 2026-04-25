{{-- resources/views/student/calendar/index.blade.php --}}
@extends('layouts.student')

@section('title', 'Academic Calendar')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    /* Modern Calendar Styles */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --holiday-color: #ef476f;
        --exam-color: #ffd166;
        --event-color: #06ffa5;
        --meeting-color: #4cc9f0;
        --deadline-color: #fb8b67;
    }

    /* Calendar Container */
    .calendar-wrapper {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        transition: all 0.3s ease;
    }

    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }

    /* FullCalendar Customization */
    .fc {
        font-family: 'Inter', sans-serif;
    }

    /* Toolbar Styles */
    .fc-toolbar {
        margin-bottom: 25px !important;
    }

    .fc-toolbar-title {
        font-size: 1.8rem !important;
        font-weight: 700 !important;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }

    .fc-button {
        border-radius: 12px !important;
        padding: 8px 16px !important;
        font-weight: 600 !important;
        text-transform: capitalize !important;
        transition: all 0.3s ease !important;
        border: none !important;
        background: #f0f2f5 !important;
        color: #4a5568 !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05) !important;
    }

    .fc-button:hover {
        background: var(--primary-gradient) !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3) !important;
    }

    .fc-button-primary {
        background: var(--primary-gradient) !important;
        color: white !important;
    }

    .fc-button-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4) !important;
    }

    .fc-button-group .fc-button {
        margin: 0 3px !important;
    }

    /* Day Grid Styles */
    .fc-daygrid-day {
        transition: all 0.2s ease;
        background: white;
        border-color: #e2e8f0 !important;
    }

    .fc-daygrid-day:hover {
        background: #f8fafc;
    }

    .fc-day-today {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%) !important;
    }

    .fc-daygrid-day-number {
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        padding: 8px !important;
        transition: all 0.2s ease;
    }

    .fc-day-today .fc-daygrid-day-number {
        background: var(--primary-gradient);
        color: white !important;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
    }

    /* Weekend Styling */
    .fc-day-sat .fc-daygrid-day-number,
    .fc-day-sun .fc-daygrid-day-number {
        color: #ef476f;
    }

    /* Event Styles */
    .fc-event {
        cursor: pointer;
        border: none !important;
        border-radius: 8px !important;
        padding: 4px 8px !important;
        margin: 2px 4px !important;
        font-size: 11px !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .fc-event:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .fc-event-title {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Event Type Specific Styles */
    .fc-event.holiday-event {
        background: linear-gradient(135deg, #ef476f 0%, #d43f63 100%) !important;
    }

    .fc-event.exam-event {
        background: linear-gradient(135deg, #ffd166 0%, #ffbe3c 100%) !important;
        color: #2d3748 !important;
    }

    .fc-event.event-event {
        background: linear-gradient(135deg, #06ffa5 0%, #00d4a0 100%) !important;
        color: #1a202c !important;
    }

    .fc-event.meeting-event {
        background: linear-gradient(135deg, #4cc9f0 0%, #3a9bc0 100%) !important;
    }

    .fc-event.deadline-event {
        background: linear-gradient(135deg, #fb8b67 0%, #e06c47 100%) !important;
    }

    /* Week View Styles */
    .fc-timegrid-slot {
        height: 60px !important;
    }

    .fc-timegrid-slot-label {
        font-size: 12px;
        font-weight: 500;
        color: #718096;
    }

    .fc-timegrid-event {
        border-radius: 10px !important;
        padding: 5px !important;
    }

    /* List View Styles */
    .fc-list-table {
        border-radius: 15px;
        overflow: hidden;
    }

    .fc-list-day-cushion {
        background: var(--primary-gradient) !important;
        color: white !important;
        padding: 12px !important;
        font-weight: 600 !important;
    }

    .fc-list-event:hover {
        background: #f8fafc !important;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
        color: #667eea;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: #2d3748;
        margin-bottom: 5px;
        line-height: 1;
    }

    .stat-label {
        font-size: 13px;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    /* Event Details Modal */
    .modal-modern .modal-content {
        border-radius: 20px;
        border: none;
        overflow: hidden;
    }

    .modal-modern .modal-header {
        padding: 20px 25px;
        border: none;
    }

    .modal-modern .modal-body {
        padding: 25px;
    }

    .modal-modern .modal-footer {
        border: none;
        padding: 20px 25px;
    }

    .event-detail-card {
        background: #f8fafc;
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.2s;
    }

    .event-detail-card:hover {
        background: #f1f5f9;
    }

    .event-detail-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #718096;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .event-detail-value {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    /* Legend */
    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #4a5568;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 6px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .calendar-wrapper {
            padding: 10px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .stat-number {
            font-size: 24px;
        }
        
        .fc-toolbar {
            flex-direction: column;
            gap: 15px;
        }
        
        .fc-toolbar-title {
            font-size: 1.3rem !important;
        }
        
        .fc-button {
            padding: 6px 12px !important;
            font-size: 12px !important;
        }
        
        .legend {
            gap: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-number" id="upcomingEventsCount">0</div>
            <div class="stat-label">Upcoming Events</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="stat-number" id="upcomingHolidaysCount">0</div>
            <div class="stat-label">Upcoming Holidays</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-number" id="upcomingExamsCount">0</div>
            <div class="stat-label">Upcoming Exams</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-number" id="nextEventDays">0</div>
            <div class="stat-label">Days to Next Event</div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="calendar-wrapper">
        <div id="calendar"></div>
        
        <!-- Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #ef476f 0%, #d43f63 100%);"></div>
                <span>Holiday</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #ffd166 0%, #ffbe3c 100%);"></div>
                <span>Exam</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #06ffa5 0%, #00d4a0 100%);"></div>
                <span>Event</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #4cc9f0 0%, #3a9bc0 100%);"></div>
                <span>Meeting</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #fb8b67 0%, #e06c47 100%);"></div>
                <span>Deadline</span>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade modal-modern" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="eventModalHeader">
                <h5 class="modal-title" id="eventTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetails">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="addToCalendarBtn" style="display: none;">
                    <i class="fas fa-calendar-plus me-1"></i> Add to My Calendar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

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
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            height: 'auto',
            firstDay: 1,
            weekNumbers: false,
            navLinks: true,
            editable: false,
            selectable: false,
            eventDisplay: 'block',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: 'short'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                list: 'List'
            },
            events: {
                url: '{{ route("student.calendar.events") }}',
                method: 'GET',
                failure: function() {
                    console.error('Error loading events');
                    updateStatistics([]);
                },
                success: function(data) {
                    updateStatistics(data);
                }
            },
            eventDidMount: function(info) {
                var eventType = info.event.extendedProps.type;
                if (eventType === 'holiday') {
                    info.el.classList.add('holiday-event');
                } else if (eventType === 'exam') {
                    info.el.classList.add('exam-event');
                } else if (eventType === 'meeting') {
                    info.el.classList.add('meeting-event');
                } else if (eventType === 'deadline') {
                    info.el.classList.add('deadline-event');
                } else {
                    info.el.classList.add('event-event');
                }
                
                // Add tooltip
                info.el.setAttribute('title', info.event.title + '\nClick for details');
            },
            eventClick: function(info) {
                var event = info.event;
                var typeColors = {
                    'holiday': '#ef476f',
                    'exam': '#ffd166',
                    'meeting': '#4cc9f0',
                    'deadline': '#fb8b67',
                    'event': '#06ffa5'
                };
                var typeColor = typeColors[event.extendedProps.type] || '#667eea';
                
                $('#eventModalHeader').css('background', 'linear-gradient(135deg, ' + typeColor + ' 0%, ' + adjustColor(typeColor, -20) + ' 100%)');
                $('#eventModalHeader').css('color', event.extendedProps.type === 'exam' ? '#2d3748' : '#fff');
                $('#eventTitle').text(event.title);
                
                var details = '<div class="event-detail-card">';
                details += '<div class="event-detail-label">Event Type</div>';
                details += '<div class="event-detail-value"><i class="fas fa-tag me-2"></i>' + (event.extendedProps.type_text || 'Event') + '</div>';
                details += '</div>';
                
                details += '<div class="event-detail-card">';
                details += '<div class="event-detail-label">Date & Time</div>';
                details += '<div class="event-detail-value"><i class="fas fa-calendar-day me-2"></i>' + event.start.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                if (event.end && event.end > event.start) {
                    details += ' - ' + event.end.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                }
                details += '</div>';
                if (event.extendedProps.time) {
                    details += '<div class="event-detail-value mt-1"><i class="fas fa-clock me-2"></i>' + event.extendedProps.time + '</div>';
                }
                details += '</div>';
                
                if (event.extendedProps.venue) {
                    details += '<div class="event-detail-card">';
                    details += '<div class="event-detail-label">Venue</div>';
                    details += '<div class="event-detail-value"><i class="fas fa-map-marker-alt me-2"></i>' + event.extendedProps.venue + '</div>';
                    details += '</div>';
                }
                
                if (event.extendedProps.description) {
                    details += '<div class="event-detail-card">';
                    details += '<div class="event-detail-label">Description</div>';
                    details += '<div class="event-detail-value">' + event.extendedProps.description + '</div>';
                    details += '</div>';
                }
                
                $('#eventDetails').html(details);
                $('#eventDetailsModal').modal('show');
            },
            loading: function(isLoading) {
                if (isLoading) {
                    $('.calendar-wrapper').css('opacity', '0.6');
                } else {
                    $('.calendar-wrapper').css('opacity', '1');
                }
            }
        });
        
        calendar.render();
        
        // Helper function to adjust color brightness
        function adjustColor(color, percent) {
            // Simple color adjustment for gradient
            return color;
        }
        
        // Update statistics function
        function updateStatistics(events) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            var upcomingEvents = events.filter(function(e) {
                var eventDate = new Date(e.start);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate >= today && e.type !== 'holiday';
            });
            
            var upcomingHolidays = events.filter(function(e) {
                var eventDate = new Date(e.start);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate >= today && e.type === 'holiday';
            });
            
            var upcomingExams = events.filter(function(e) {
                var eventDate = new Date(e.start);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate >= today && e.type === 'exam';
            });
            
            // Animate counter
            animateNumber($('#upcomingEventsCount'), upcomingEvents.length);
            animateNumber($('#upcomingHolidaysCount'), upcomingHolidays.length);
            animateNumber($('#upcomingExamsCount'), upcomingExams.length);
            
            if (upcomingEvents.length > 0) {
                var nextEvent = upcomingEvents.reduce(function(a, b) {
                    return new Date(a.start) < new Date(b.start) ? a : b;
                });
                var daysDiff = Math.ceil((new Date(nextEvent.start) - today) / (1000 * 60 * 60 * 24));
                animateNumber($('#nextEventDays'), daysDiff >= 0 ? daysDiff : 0);
            } else {
                animateNumber($('#nextEventDays'), 0);
            }
        }
        
        // Animate number counter
        function animateNumber(element, target) {
            var current = parseInt(element.text()) || 0;
            if (current === target) return;
            
            var duration = 500;
            var step = (target - current) / (duration / 16);
            var interval = setInterval(function() {
                current += step;
                if ((step > 0 && current >= target) || (step < 0 && current <= target)) {
                    element.text(target);
                    clearInterval(interval);
                } else {
                    element.text(Math.round(current));
                }
            }, 16);
        }
    });
</script>
@endpush