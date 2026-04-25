<?php
// app/Http/Controllers/Api/Student/CalendarController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\CalendarEvent;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends BaseController
{
    /**
     * Get all calendar events (including holidays)
     */
    public function getEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $start = $request->start;
        $end = $request->end;
        $student = Auth::user()->student;

        // Get calendar events for student's audience
        $events = CalendarEvent::where(function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end]);
            })
            ->where('is_active', true)
            ->whereIn('audience', ['all', 'students'])
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_date' => $event->start_date->toDateString(),
                    'end_date' => $event->end_date?->toDateString(),
                    'start_time' => $event->start_time ? Carbon::parse($event->start_time)->format('h:i A') : null,
                    'end_time' => $event->end_time ? Carbon::parse($event->end_time)->format('h:i A') : null,
                    'type' => $event->type,
                    'type_text' => CalendarEvent::TYPES[$event->type] ?? ucfirst($event->type),
                    'type_color' => $this->getEventColor($event->type),
                    'venue' => $event->venue,
                    'audience' => $event->audience,
                    'repeat_type' => $event->repeat_type,
                    'is_all_day' => !$event->start_time,
                ];
            });

        // Get holidays
        $holidays = Holiday::whereBetween('date', [$start, $end])
            ->where('is_active', true)
            ->get()
            ->map(function($holiday) {
                return [
                    'id' => 'holiday_' . $holiday->id,
                    'title' => $holiday->name,
                    'description' => $holiday->description,
                    'start_date' => $holiday->date->toDateString(),
                    'end_date' => $holiday->date->toDateString(),
                    'type' => 'holiday',
                    'type_text' => 'Holiday',
                    'type_color' => '#dc3545',
                    'holiday_type' => $holiday->type,
                    'holiday_type_text' => Holiday::TYPES[$holiday->type] ?? ucfirst($holiday->type),
                    'is_optional' => $holiday->is_optional,
                    'is_all_day' => true,
                ];
            });

        return $this->sendResponse([
            'events' => $events,
            'holidays' => $holidays,
            'all_events' => $events->concat($holidays),
        ], 'Calendar events retrieved successfully');
    }

    /**
     * Get upcoming events and holidays
     */
    public function upcoming(Request $request)
    {
        $limit = $request->limit ?? 10;
        $student = Auth::user()->student;

        // Upcoming calendar events
        $events = CalendarEvent::where('start_date', '>=', Carbon::today())
            ->where('is_active', true)
            ->whereIn('audience', ['all', 'students'])
            ->orderBy('start_date')
            ->limit($limit)
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'start_date' => $event->start_date->toDateString(),
                    'end_date' => $event->end_date?->toDateString(),
                    'start_time' => $event->start_time ? Carbon::parse($event->start_time)->format('h:i A') : null,
                    'type' => $event->type,
                    'type_text' => CalendarEvent::TYPES[$event->type] ?? ucfirst($event->type),
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
                    'id' => $holiday->id,
                    'name' => $holiday->name,
                    'description' => $holiday->description,
                    'date' => $holiday->date->toDateString(),
                    'type' => $holiday->type,
                    'type_text' => Holiday::TYPES[$holiday->type] ?? ucfirst($holiday->type),
                    'days_left' => Carbon::today()->diffInDays($holiday->date, false),
                ];
            });

        return $this->sendResponse([
            'upcoming_events' => $events,
            'upcoming_holidays' => $holidays,
            'total_events' => $events->count(),
            'total_holidays' => $holidays->count(),
        ], 'Upcoming events retrieved successfully');
    }

    /**
     * Get all holidays for the academic year
     */
    public function holidays(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $student = Auth::user()->student;

        $holidays = Holiday::whereYear('date', $year)
            ->where('is_active', true)
            ->orderBy('date')
            ->get()
            ->groupBy(function($holiday) {
                return $holiday->date->format('F');
            })
            ->map(function($items, $month) {
                return [
                    'month' => $month,
                    'holidays' => $items->map(function($holiday) {
                        return [
                            'id' => $holiday->id,
                            'name' => $holiday->name,
                            'description' => $holiday->description,
                            'date' => $holiday->date->toDateString(),
                            'day' => $holiday->date->format('l'),
                            'type' => $holiday->type,
                            'type_text' => Holiday::TYPES[$holiday->type] ?? ucfirst($holiday->type),
                            'is_optional' => $holiday->is_optional,
                        ];
                    }),
                ];
            });

        $statistics = [
            'total_holidays' => Holiday::whereYear('date', $year)->where('is_active', true)->count(),
            'public_holidays' => Holiday::whereYear('date', $year)->where('type', 'public')->where('is_active', true)->count(),
            'school_holidays' => Holiday::whereYear('date', $year)->where('type', 'school')->where('is_active', true)->count(),
            'national_holidays' => Holiday::whereYear('date', $year)->where('type', 'national')->where('is_active', true)->count(),
            'festivals' => Holiday::whereYear('date', $year)->where('type', 'festival')->where('is_active', true)->count(),
        ];

        return $this->sendResponse([
            'year' => $year,
            'statistics' => $statistics,
            'holidays_by_month' => $holidays,
        ], 'Holidays retrieved successfully');
    }

    /**
     * Get event color based on type
     */
    private function getEventColor($type)
    {
        return match($type) {
            'holiday' => '#dc3545',
            'exam' => '#ffc107',
            'meeting' => '#17a2b8',
            'deadline' => '#fd7e14',
            default => '#007bff',
        };
    }
}