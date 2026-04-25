{{-- resources/views/teacher/calendar/index.blade.php --}}
@extends('layouts.teacher')

@section('title', 'Calendar')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .fc-event:hover {
        transform: scale(1.02);
    }
    .fc-day-today {
        background-color: rgba(102, 126, 234, 0.05) !important;
    }
    .fc-toolbar-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1a1e2b;
    }
    .fc-button-primary {
        background-color: #4361ee !important;
        border-color: #4361ee !important;
    }
    .fc-button-primary:hover {
        background-color: #3a56d4 !important;
    }
    
    /* Stats Cards */
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    /* Event Detail Modal */
    .event-detail-card {
        background: #f8fafc;
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 15px;
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
</style>
@endpush

@section('content')
<div class="animate-fadeInUp">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="stat-number" id="todayClassesCount">0</div>
            <div class="stat-label">Today's Classes</div>
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
                <i class="fas fa-gift"></i>
            </div>
            <div class="stat-number" id="upcomingHolidaysCount">0</div>
            <div class="stat-label">Upcoming Holidays</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-number" id="nextClassDays">0</div>
            <div class="stat-label">Days to Next Class</div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-calendar-alt me-2"></i> My Calendar
            <span class="float-end">
                <small><i class="fas fa-info-circle me-1"></i> Click on event to view details</small>
            </span>
        </div>
        <div class="card-body p-3">
            <div id="calendar"></div>
            
            <!-- Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #17a2b8;"></div>
                    <span>Classes</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffc107;"></div>
                    <span>Exams</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #007bff;"></div>
                    <span>Events</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #dc3545;"></div>
                    <span>Holidays</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
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
            navLinks: true,
            editable: false,
            selectable: false,
            nowIndicator: true,
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
                url: '{{ route("teacher.calendar.events") }}',
                method: 'GET',
                failure: function() {
                    console.error('Error loading events');
                    updateStatistics([]);
                },
                success: function(events) {
                    updateStatistics(events);
                }
            },
            eventDidMount: function(info) {
                // Add tooltip
                info.el.setAttribute('title', info.event.title + '\nClick for details');
            },
            eventClick: function(info) {
                var event = info.event;
                var typeColors = {
                    'class': '#17a2b8',
                    'exam': '#ffc107',
                    'event': '#007bff',
                    'holiday': '#dc3545'
                };
                var typeColor = typeColors[event.extendedProps.type] || '#667eea';
                
                $('#eventModalHeader').css('background', typeColor);
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
            }
        });
        
        calendar.render();
        
        // Update statistics function
        function updateStatistics(events) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            var todayClasses = events.filter(function(e) {
                var eventDate = new Date(e.start);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate.getTime() === today.getTime() && e.type === 'class';
            });
            
            var upcomingExams = events.filter(function(e) {
                var eventDate = new Date(e.start);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate >= today && e.type === 'exam';
            });
            
            var upcomingHolidays = events.filter(function(e) {
                var eventDate = new Date(e.start);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate >= today && e.type === 'holiday';
            });
            
            var nextClasses = events.filter(function(e) {
                var eventDate = new Date(e.start);
                eventDate.setHours(0, 0, 0, 0);
                return eventDate >= today && e.type === 'class';
            });
            
            $('#todayClassesCount').text(todayClasses.length);
            $('#upcomingExamsCount').text(upcomingExams.length);
            $('#upcomingHolidaysCount').text(upcomingHolidays.length);
            
            if (nextClasses.length > 0) {
                var nextClass = nextClasses.reduce(function(a, b) {
                    return new Date(a.start) < new Date(b.start) ? a : b;
                });
                var daysDiff = Math.ceil((new Date(nextClass.start) - today) / (1000 * 60 * 60 * 24));
                $('#nextClassDays').text(daysDiff >= 0 ? daysDiff : 0);
            } else {
                $('#nextClassDays').text(0);
            }
        }
    });
</script>
@endpush