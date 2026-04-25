<?php
// app/Http/Controllers/Teacher/CalendarController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Holiday;
use App\Models\Timetable;
use App\Models\ExamSchedule;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Display teacher calendar
     */
    public function index()
    {
        return view('teacher.calendar.index');
    }

    /**
     * Get calendar events for teacher
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

        // Get class IDs taught by this teacher
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();

        // 1. Timetable events (classes)
        $timetableEvents = Timetable::where('teacher_id', $teacher->id)
            ->whereBetween('exam_date', [$start, $end])
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->get()
            ->map(function($timetable) {
                return [
                    'id' => 'timetable_' . $timetable->id,
                    'title' => $timetable->subject->name . ' - ' . $timetable->class->name . ' (' . $timetable->section->name . ')',
                    'start' => $timetable->exam_date->format('Y-m-d') . ($timetable->timeSlot ? 'T' . $timetable->timeSlot->start_time : ''),
                    'end' => $timetable->exam_date->format('Y-m-d') . ($timetable->timeSlot ? 'T' . $timetable->timeSlot->end_time : ''),
                    'color' => '#17a2b8',
                    'type' => 'class',
                    'type_text' => 'Class',
                    'venue' => $timetable->room_number,
                    'description' => 'Class: ' . $timetable->class->name . ' - Section ' . $timetable->section->name,
                ];
            });

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
                    'venue' => $exam->room_number,
                    'description' => 'Exam for ' . $exam->class->name . ' - Section ' . $exam->section->name,
                ];
            });

        // 3. General calendar events (school events, meetings)
        $calendarEvents = CalendarEvent::whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
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
                    'type_text' => CalendarEvent::TYPES[$event->type] ?? ucfirst($event->type),
                    'venue' => $event->venue,
                    'description' => $event->description,
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
                    'description' => $holiday->description,
                    'allDay' => true,
                ];
            });

        // Merge all events
        $allEvents = $timetableEvents
            ->concat($examEvents)
            ->concat($calendarEvents)
            ->concat($holidayEvents);

        return response()->json($allEvents);
    }

    /**
     * Get upcoming events for teacher
     */
    public function upcoming(Request $request)
    {
        $limit = $request->limit ?? 10;
        $teacher = Auth::user();

        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();

        $upcoming = [];

        // Upcoming classes from timetable
        $classes = Timetable::where('teacher_id', $teacher->id)
            ->where('exam_date', '>=', Carbon::today())
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->orderBy('exam_date')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'class',
                    'title' => $item->subject->name,
                    'class' => $item->class->name,
                    'section' => $item->section->name,
                    'date' => $item->exam_date->format('Y-m-d'),
                    'time' => $item->timeSlot ? $item->timeSlot->time_range : null,
                    'venue' => $item->room_number,
                    'days_left' => Carbon::today()->diffInDays($item->exam_date, false),
                ];
            });

        // Upcoming exams
        $exams = ExamSchedule::whereHas('classSubject', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->where('exam_date', '>=', Carbon::today())
            ->with(['exam', 'subject', 'class', 'section'])
            ->orderBy('exam_date')
            ->limit($limit)
            ->get()
            ->map(function($exam) {
                return [
                    'type' => 'exam',
                    'title' => $exam->exam->name,
                    'subject' => $exam->subject->name,
                    'class' => $exam->class->name,
                    'section' => $exam->section->name,
                    'date' => $exam->exam_date->format('Y-m-d'),
                    'time' => Carbon::parse($exam->start_time)->format('h:i A') . ' - ' . Carbon::parse($exam->end_time)->format('h:i A'),
                    'venue' => $exam->room_number,
                    'days_left' => Carbon::today()->diffInDays($exam->exam_date, false),
                ];
            });

        // Upcoming events
        $events = CalendarEvent::where('start_date', '>=', Carbon::today())
            ->where('is_active', true)
            ->whereIn('audience', ['all', 'teachers'])
            ->orderBy('start_date')
            ->limit($limit)
            ->get()
            ->map(function($event) {
                return [
                    'type' => $event->type,
                    'title' => $event->title,
                    'date' => $event->start_date->format('Y-m-d'),
                    'time' => $event->start_time ? Carbon::parse($event->start_time)->format('h:i A') : 'All Day',
                    'venue' => $event->venue,
                    'days_left' => Carbon::today()->diffInDays($event->start_date, false),
                ];
            });

        // Upcoming holidays
        $holidays = Holiday::where('date', '>=', Carbon::today())
            ->where('is_active', true)
            ->orderBy('date')
            ->limit($limit)
            ->get()
            ->map(function($holiday) {
                return [
                    'type' => 'holiday',
                    'title' => $holiday->name,
                    'date' => $holiday->date->format('Y-m-d'),
                    'days_left' => Carbon::today()->diffInDays($holiday->date, false),
                ];
            });

        // Merge and sort by date
        $upcoming = $classes->concat($exams)->concat($events)->concat($holidays)
            ->sortBy('date')
            ->take($limit)
            ->values();

        return response()->json([
            'upcoming_events' => $upcoming,
            'total' => $upcoming->count(),
        ]);
    }

    /**
     * Get teacher's weekly schedule
     */
    public function weeklySchedule(Request $request)
    {
        $teacher = Auth::user();
        $weekStart = $request->week_start ? Carbon::parse($request->week_start) : Carbon::now()->startOfWeek();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
            ];
        }

        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->whereBetween('exam_date', [$weekStart->format('Y-m-d'), $weekStart->copy()->addDays(6)->format('Y-m-d')])
            ->with(['class', 'section', 'subject', 'timeSlot'])
            ->get()
            ->groupBy('exam_date');

        $schedule = [];
        foreach ($days as $day) {
            $dayEvents = $timetable[$day['date']] ?? collect();
            $schedule[] = [
                'date' => $day['date'],
                'day_name' => $day['day_name'],
                'events' => $dayEvents->map(function($event) {
                    return [
                        'time' => $event->timeSlot ? $event->timeSlot->time_range : null,
                        'subject' => $event->subject->name,
                        'class' => $event->class->name,
                        'section' => $event->section->name,
                        'room' => $event->room_number,
                    ];
                }),
            ];
        }

        return response()->json($schedule);
    }
}