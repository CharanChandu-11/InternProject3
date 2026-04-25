<?php
// app/Http/Controllers/Api/Teacher/CalendarController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\CalendarEvent;
use App\Models\Holiday;
use App\Models\Timetable;
use App\Models\ExamSchedule;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends BaseController
{
    /**
     * Get weekly schedule for teacher based on day_of_week
     */
    public function weeklySchedule(Request $request)
    {
        $teacher = Auth::user();
        
        // Get week start date (default to current week starting Monday)
        $weekStart = $request->week_start 
            ? Carbon::parse($request->week_start)->startOfWeek() 
            : Carbon::now()->startOfWeek();
        
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get all timetables for this teacher (no date filtering since we use day_of_week)
        $timetables = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->get();

        // Prepare days of the week
        $days = [];
        $currentTime = Carbon::now();
        $currentTimeFormatted = $currentTime->format('H:i:s');
        $currentClass = null;
        $todayString = $currentTime->toDateString();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dateString = $date->toDateString();
            
            // Get the day name in lowercase to match database
            $dayOfWeek = strtolower($date->format('l'));
            
            // Filter timetables for this day of week
            $dayEvents = $timetables->filter(function($timetable) use ($dayOfWeek) {
                return $timetable->day_of_week === $dayOfWeek;
            });
            
            // Sort events by time slot
            $sortedEvents = $dayEvents->sortBy(function($event) {
                return $event->timeSlot->start_time ?? '00:00:00';
            });
            
            // Check for current class on today
            if ($date->isToday()) {
                foreach ($sortedEvents as $event) {
                    if ($event->timeSlot) {
                        $start = $event->timeSlot->start_time;
                        $end = $event->timeSlot->end_time;
                        
                        if ($start <= $currentTimeFormatted && $end >= $currentTimeFormatted) {
                            $currentClass = $event;
                            break;
                        }
                    }
                }
            }
            
            $days[] = [
                'date' => $dateString,
                'day_name' => $date->format('l'),
                'day_short' => $date->format('D'),
                'day_of_week' => $dayOfWeek,
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'classes' => $sortedEvents->map(function($event) {
                    return [
                        'id' => $event->id,
                        'time_slot' => $event->timeSlot ? [
                            'id' => $event->timeSlot->id,
                            'name' => $event->timeSlot->name,
                            'start_time' => Carbon::parse($event->timeSlot->start_time)->format('h:i A'),
                            'end_time' => Carbon::parse($event->timeSlot->end_time)->format('h:i A'),
                            'time_range' => $event->timeSlot->time_range,
                        ] : null,
                        'subject' => [
                            'id' => $event->subject->id ?? null,
                            'name' => $event->subject->name ?? 'N/A',
                            'code' => $event->subject->code ?? 'N/A',
                        ],
                        'class' => [
                            'id' => $event->class->id ?? null,
                            'name' => $event->class->name ?? 'N/A',
                        ],
                        'section' => [
                            'id' => $event->section->id ?? null,
                            'name' => $event->section->name ?? 'N/A',
                        ],
                        'room_number' => $event->room_number,
                    ];
                }),
                'classes_count' => $sortedEvents->count(),
            ];
        }

        // Get week range text
        $weekRange = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y');

        return $this->sendResponse([
            'week_range' => $weekRange,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'current_time' => $currentTime->format('h:i A'),
            'current_class' => $currentClass ? [
                'subject' => $currentClass->subject->name ?? 'N/A',
                'class' => $currentClass->class->name ?? 'N/A',
                'section' => $currentClass->section->name ?? 'N/A',
                'room' => $currentClass->room_number,
                'time' => $currentClass->timeSlot ? $currentClass->timeSlot->time_range : null,
                'ends_at' => $currentClass->timeSlot ? Carbon::parse($currentClass->timeSlot->end_time)->format('h:i A') : null,
            ] : null,
            'schedule' => $days,
            'summary' => [
                'total_classes' => $timetables->count(),
                'days_with_classes' => collect($days)->filter(function($day) {
                    return $day['classes_count'] > 0;
                })->count(),
                'average_classes_per_day' => $timetables->count() > 0 
                    ? round($timetables->count() / 7, 1) 
                    : 0,
            ],
        ], 'Weekly schedule retrieved successfully');
    }

    /**
     * Get all calendar events for teacher
     */
    public function getEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $start = $request->start;
        $end = $request->end;
        $teacher = Auth::user();
        
        $allEvents = [];

        // 1. Timetable events (classes) - Convert recurring weekly schedule to date range
        $timetables = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->get();
        
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        
        // Generate events for each date in range
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayOfWeek = strtolower($currentDate->format('l'));
            
            // Find timetables for this day of week
            $dayTimetables = $timetables->filter(function($timetable) use ($dayOfWeek) {
                return $timetable->day_of_week === $dayOfWeek;
            });
            
            foreach ($dayTimetables as $timetable) {
                $allEvents[] = [
                    'id' => 'timetable_' . $timetable->id . '_' . $currentDate->format('Ymd'),
                    'title' => $timetable->subject->name . ' - ' . $timetable->class->name . ' (' . $timetable->section->name . ')',
                    'start' => $currentDate->format('Y-m-d') . ($timetable->timeSlot ? 'T' . $timetable->timeSlot->start_time : ''),
                    'end' => $currentDate->format('Y-m-d') . ($timetable->timeSlot ? 'T' . $timetable->timeSlot->end_time : ''),
                    'color' => '#17a2b8',
                    'type' => 'class',
                    'type_text' => 'Class',
                    'extendedProps' => [
                        'venue' => $timetable->room_number,
                        'description' => 'Class: ' . $timetable->class->name . ' - Section ' . $timetable->section->name,
                        'class_name' => $timetable->class->name,
                        'section_name' => $timetable->section->name,
                        'subject_name' => $timetable->subject->name,
                        'time_range' => $timetable->timeSlot ? $timetable->timeSlot->time_range : null,
                    ],
                    'allDay' => false,
                ];
            }
            
            $currentDate->addDay();
        }

        // 2. Exam schedules
        $examEvents = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereBetween('exam_date', [$start, $end])
            ->with(['exam', 'subject', 'class', 'section'])
            ->get()
            ->map(function($exam) {
                return [
                    'id' => 'exam_' . $exam->id,
                    'title' => $exam->exam->name . ' - ' . $exam->subject->name,
                    'start' => $exam->exam_date->format('Y-m-d') . 'T' . $exam->start_time,
                    'end' => $exam->exam_date->format('Y-m-d') . 'T' . $exam->end_time,
                    'color' => '#ffc107',
                    'textColor' => '#000',
                    'type' => 'exam',
                    'type_text' => 'Exam',
                    'extendedProps' => [
                        'venue' => $exam->room_number,
                        'description' => 'Exam for ' . $exam->class->name . ' - Section ' . $exam->section->name,
                        'exam_name' => $exam->exam->name,
                        'subject_name' => $exam->subject->name,
                        'class_name' => $exam->class->name,
                        'section_name' => $exam->section->name,
                        'total_marks' => $exam->total_marks,
                        'passing_marks' => $exam->passing_marks,
                    ],
                    'allDay' => false,
                ];
            });

        // 3. General calendar events
        $calendarEvents = CalendarEvent::where(function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end]);
            })
            ->where('is_active', true)
            ->whereIn('audience', ['all', 'teachers'])
            ->get()
            ->map(function($event) {
                return [
                    'id' => 'event_' . $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date->format('Y-m-d') . ($event->start_time ? 'T' . $event->start_time : ''),
                    'end' => ($event->end_date ? $event->end_date->format('Y-m-d') : $event->start_date->format('Y-m-d')) . ($event->end_time ? 'T' . $event->end_time : ''),
                    'color' => $event->color ?? '#007bff',
                    'type' => $event->type,
                    'type_text' => \App\Models\CalendarEvent::TYPES[$event->type] ?? ucfirst($event->type),
                    'extendedProps' => [
                        'venue' => $event->venue,
                        'description' => $event->description,
                        'audience' => $event->audience,
                    ],
                    'allDay' => !$event->start_time,
                ];
            });

        // 4. Holidays
        $holidayEvents = Holiday::whereBetween('date', [$start, $end])
            ->where('is_active', true)
            ->get()
            ->map(function($holiday) {
                return [
                    'id' => 'holiday_' . $holiday->id,
                    'title' => $holiday->name,
                    'start' => $holiday->date->format('Y-m-d'),
                    'end' => $holiday->date->format('Y-m-d'),
                    'color' => '#dc3545',
                    'type' => 'holiday',
                    'type_text' => 'Holiday',
                    'extendedProps' => [
                        'description' => $holiday->description,
                        'holiday_type' => $holiday->type,
                    ],
                    'allDay' => true,
                ];
            });

        // Merge all events
        $allEventsCollection = collect($allEvents)
            ->concat($examEvents)
            ->concat($calendarEvents)
            ->concat($holidayEvents)
            ->values();

        return $this->sendResponse($allEventsCollection, 'Calendar events retrieved successfully');
    }

    /**
     * Get upcoming events for teacher
     */
    public function upcoming(Request $request)
    {
        $limit = $request->limit ?? 10;
        $teacher = Auth::user();
        $today = Carbon::today();
        $nextMonth = Carbon::today()->addDays(30);

        $upcomingEvents = [];

        // Get all timetables for the teacher
        $timetables = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->get();

        // Generate upcoming classes for the next 30 days
        $currentDate = $today->copy();
        $generatedClasses = [];
        
        while ($currentDate <= $nextMonth && count($generatedClasses) < $limit) {
            $dayOfWeek = strtolower($currentDate->format('l'));
            $dayTimetables = $timetables->filter(function($timetable) use ($dayOfWeek) {
                return $timetable->day_of_week === $dayOfWeek;
            });
            
            foreach ($dayTimetables as $timetable) {
                $generatedClasses[] = [
                    'id' => 'timetable_' . $timetable->id . '_' . $currentDate->format('Ymd'),
                    'type' => 'class',
                    'type_text' => 'Class',
                    'title' => $timetable->subject->name,
                    'class_name' => $timetable->class->name,
                    'section_name' => $timetable->section->name,
                    'subject_name' => $timetable->subject->name,
                    'date' => $currentDate->toDateString(),
                    'time' => $timetable->timeSlot ? $timetable->timeSlot->time_range : null,
                    'venue' => $timetable->room_number,
                    'days_left' => $today->diffInDays($currentDate, false),
                ];
            }
            
            $currentDate->addDay();
        }

        // Sort by date
        $sortedClasses = collect($generatedClasses)->sortBy('date')->take($limit)->values();

        // Upcoming exams
        $exams = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('exam_date', '>=', $today)
            ->with(['exam', 'subject', 'class', 'section'])
            ->orderBy('exam_date')
            ->limit($limit)
            ->get()
            ->map(function($exam) use ($today) {
                return [
                    'id' => 'exam_' . $exam->id,
                    'type' => 'exam',
                    'type_text' => 'Exam',
                    'title' => $exam->exam->name,
                    'subject_name' => $exam->subject->name,
                    'class_name' => $exam->class->name,
                    'section_name' => $exam->section->name,
                    'date' => $exam->exam_date->toDateString(),
                    'time' => Carbon::parse($exam->start_time)->format('h:i A') . ' - ' . Carbon::parse($exam->end_time)->format('h:i A'),
                    'venue' => $exam->room_number,
                    'days_left' => $today->diffInDays($exam->exam_date, false),
                ];
            });

        // Upcoming events
        $events = CalendarEvent::where('start_date', '>=', $today)
            ->where('is_active', true)
            ->whereIn('audience', ['all', 'teachers'])
            ->orderBy('start_date')
            ->limit($limit)
            ->get()
            ->map(function($event) use ($today) {
                return [
                    'id' => 'event_' . $event->id,
                    'type' => $event->type,
                    'type_text' => \App\Models\CalendarEvent::TYPES[$event->type] ?? ucfirst($event->type),
                    'title' => $event->title,
                    'date' => $event->start_date->toDateString(),
                    'time' => $event->start_time ? Carbon::parse($event->start_time)->format('h:i A') : 'All Day',
                    'venue' => $event->venue,
                    'days_left' => $today->diffInDays($event->start_date, false),
                ];
            });

        // Upcoming holidays
        $holidays = Holiday::where('date', '>=', $today)
            ->where('is_active', true)
            ->orderBy('date')
            ->limit($limit)
            ->get()
            ->map(function($holiday) use ($today) {
                return [
                    'id' => 'holiday_' . $holiday->id,
                    'type' => 'holiday',
                    'type_text' => 'Holiday',
                    'title' => $holiday->name,
                    'date' => $holiday->date->toDateString(),
                    'days_left' => $today->diffInDays($holiday->date, false),
                ];
            });

        // Merge all upcoming events
        $allUpcoming = $sortedClasses->concat($exams)->concat($events)->concat($holidays)
            ->sortBy('date')
            ->take($limit)
            ->values();

        $stats = [
            'total_classes' => $sortedClasses->count(),
            'total_exams' => $exams->count(),
            'total_events' => $events->count(),
            'total_holidays' => $holidays->count(),
            'total_upcoming' => $allUpcoming->count(),
        ];

        return $this->sendResponse([
            'upcoming_events' => $allUpcoming,
            'statistics' => $stats,
        ], 'Upcoming events retrieved successfully');
    }
}