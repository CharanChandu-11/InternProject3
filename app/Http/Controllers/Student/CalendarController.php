<?php
// app/Http/Controllers/Student/CalendarController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        return view('student.calendar.index');
    }

    public function getEvents(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $student = Auth::user()->student;

        // Get calendar events for student's audience
        $events = CalendarEvent::whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->where('is_active', true)
            ->whereIn('audience', ['all', 'students'])
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date->format('Y-m-d'),
                    'end' => $event->end_date ? $event->end_date->format('Y-m-d') : $event->start_date->format('Y-m-d'),
                    'color' => $event->color ?? $this->getEventColor($event->type),
                    'description' => $event->description,
                    'type' => $event->type,
                    'type_text' => \App\Models\CalendarEvent::TYPES[$event->type] ?? ucfirst($event->type),
                    'venue' => $event->venue,
                    'time' => $event->start_time ? Carbon::parse($event->start_time)->format('h:i A') : null,
                    'allDay' => !$event->start_time,
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
                    'start' => $holiday->date->format('Y-m-d'),
                    'end' => $holiday->date->format('Y-m-d'),
                    'color' => '#dc3545',
                    'description' => $holiday->description,
                    'type' => 'holiday',
                    'type_text' => 'Holiday',
                    'allDay' => true,
                ];
            });

        return response()->json($events->concat($holidays));
    }

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