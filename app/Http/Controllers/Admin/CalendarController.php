<?php
// app/Http/Controllers/Admin/CalendarController.php

namespace App\Http\Controllers\Admin;

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
        return view('admin.calendar.index');
    }

    public function getEvents(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        // Get calendar events
        $events = CalendarEvent::whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->active()
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
                    'type_text' => CalendarEvent::TYPES[$event->type],
                    'venue' => $event->venue,
                    'time' => $event->start_time ? Carbon::parse($event->start_time)->format('h:i A') : null,
                    'allDay' => !$event->start_time,
                ];
            });

        // Get holidays
        $holidays = Holiday::whereBetween('date', [$start, $end])
            ->active()
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

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(CalendarEvent::TYPES)),
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'venue' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $event = CalendarEvent::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'venue' => $request->venue,
            'audience' => $request->audience ?? 'all',
            'color' => $request->color,
            'repeat_type' => $request->repeat_type ?? 'none',
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function update(Request $request, CalendarEvent $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(CalendarEvent::TYPES)),
            'start_date' => 'required|date',
        ]);

        $event->update($request->all());

        return response()->json(['success' => true]);
    }

    public function destroy(CalendarEvent $event)
    {
        $event->delete();
        return response()->json(['success' => true]);
    }
}