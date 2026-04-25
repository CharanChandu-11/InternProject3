{{-- resources/views/admin/calendar/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Academic Calendar')

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
        background-color: rgba(67, 97, 238, 0.05) !important;
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
    .fc-button-primary:focus {
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.5) !important;
    }
    .holiday-event {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .exam-event {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #000;
    }
    .meeting-event {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    .deadline-event {
        background-color: #fd7e14;
        border-color: #fd7e14;
    }
    .event-card {
        border-left: 4px solid;
        margin-bottom: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .event-card:hover {
        background: #e9ecef;
    }
</style>
@endpush

@section('content')
<div class="animate-fadeInUp">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-calendar-alt me-2"></i> Academic Calendar
            <div class="float-end">
                <button type="button" class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="fas fa-plus me-1"></i> Add Event
                </button>
                <a href="{{ route('admin.holidays.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-gift me-1"></i> Manage Holidays
                </a>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="eventForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Event</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Enter event title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                @foreach(\App\Models\CalendarEvent::TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                            <small class="text-muted">Leave empty for single day event</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Venue</label>
                            <input type="text" name="venue" class="form-control" placeholder="Event location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Audience</label>
                            <select name="audience" class="form-select">
                                @foreach(\App\Models\CalendarEvent::AUDIENCE as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Repeat Type</label>
                            <select name="repeat_type" class="form-select">
                                @foreach(\App\Models\CalendarEvent::REPEAT_TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Color</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#4361ee">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Event description..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="eventModalHeader">
                <h5 class="modal-title" id="eventTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetails">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;">
                    <i class="fas fa-trash me-1"></i> Delete Event
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
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
            events: '{{ route("admin.calendar.events") }}',
            editable: false,
            selectable: true,
            eventDidMount: function(info) {
                if (info.event.extendedProps.type === 'holiday') {
                    info.el.style.backgroundColor = '#dc3545';
                    info.el.style.borderColor = '#dc3545';
                } else if (info.event.extendedProps.type === 'exam') {
                    info.el.style.backgroundColor = '#ffc107';
                    info.el.style.borderColor = '#ffc107';
                    info.el.style.color = '#000';
                } else if (info.event.extendedProps.type === 'meeting') {
                    info.el.style.backgroundColor = '#17a2b8';
                    info.el.style.borderColor = '#17a2b8';
                } else if (info.event.extendedProps.type === 'deadline') {
                    info.el.style.backgroundColor = '#fd7e14';
                    info.el.style.borderColor = '#fd7e14';
                }
            },
            eventClick: function(info) {
                var event = info.event;
                $('#eventTitle').text(event.title);
                
                var typeColors = {
                    'holiday': '#dc3545',
                    'exam': '#ffc107',
                    'meeting': '#17a2b8',
                    'deadline': '#fd7e14',
                    'event': '#007bff'
                };
                var typeColor = typeColors[event.extendedProps.type] || '#007bff';
                $('#eventModalHeader').css('background-color', typeColor).css('color', event.extendedProps.type === 'exam' ? '#000' : '#fff');
                
                var details = '<div class="event-card" style="border-left-color: ' + typeColor + '">';
                details += '<div class="mb-2"><strong><i class="fas fa-tag me-2"></i>Type:</strong> ' + (event.extendedProps.type_text || 'Event') + '</div>';
                details += '<div class="mb-2"><strong><i class="fas fa-calendar-day me-2"></i>Date:</strong> ' + event.start.toDateString();
                if (event.end && event.end > event.start) {
                    details += ' - ' + event.end.toDateString();
                }
                details += '</div>';
                if (event.extendedProps.time) {
                    details += '<div class="mb-2"><strong><i class="fas fa-clock me-2"></i>Time:</strong> ' + event.extendedProps.time + '</div>';
                }
                if (event.extendedProps.venue) {
                    details += '<div class="mb-2"><strong><i class="fas fa-map-marker-alt me-2"></i>Venue:</strong> ' + event.extendedProps.venue + '</div>';
                }
                if (event.extendedProps.description) {
                    details += '<div class="mb-2"><strong><i class="fas fa-align-left me-2"></i>Description:</strong><br>' + event.extendedProps.description + '</div>';
                }
                details += '</div>';
                $('#eventDetails').html(details);
                
                if (!event.id.toString().startsWith('holiday_')) {
                    $('#deleteEventBtn').show().off('click').on('click', function() {
                        Swal.fire({
                            title: 'Delete Event?',
                            text: 'This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '{{ url("admin/calendar/events") }}/' + event.id,
                                    type: 'DELETE',
                                    data: { _token: '{{ csrf_token() }}' },
                                    success: function(response) {
                                        calendar.refetchEvents();
                                        $('#eventDetailsModal').modal('hide');
                                        $('#toastMessage').text('Event deleted successfully');
                                        $('#liveToast').toast('show');
                                    },
                                    error: function() {
                                        $('#toastMessage').text('Error deleting event');
                                        $('#liveToast').toast('show');
                                    }
                                });
                            }
                        });
                    });
                } else {
                    $('#deleteEventBtn').hide();
                }
                
                $('#eventDetailsModal').modal('show');
            },
            dateClick: function(info) {
                $('#addEventModal input[name="start_date"]').val(info.dateStr);
                $('#addEventModal input[name="end_date"]').val(info.dateStr);
                $('#addEventModal').modal('show');
            }
        });
        calendar.render();
        
        // Handle form submission
        $('#eventForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("admin.calendar.events.store") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        calendar.refetchEvents();
                        $('#addEventModal').modal('hide');
                        $('#eventForm')[0].reset();
                        $('#toastMessage').text('Event added successfully');
                        $('#liveToast').toast('show');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors;
                    var errorMsg = 'Error adding event';
                    if (errors) {
                        errorMsg = Object.values(errors).flat().join('\n');
                    }
                    $('#toastMessage').text(errorMsg);
                    $('#liveToast').toast('show');
                }
            });
        });
    });
</script>
@endpush