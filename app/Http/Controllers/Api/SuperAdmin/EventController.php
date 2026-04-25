<?php
// app/Http/Controllers/Api/SuperAdmin/EventController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends BaseController
{
    public function index()
    {
        $events = Event::with('creator')->orderBy('start_date')->get();
        return $this->sendResponse($events, 'Events retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:academic,cultural,sports,meeting,holiday,field_trip',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'venue' => 'nullable|string',
            'audience' => 'required|in:all,students,teachers,staff',
            'participants' => 'nullable|array',
            'image' => 'nullable|image',
        ]);
        $validated['created_by'] = auth()->id();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $validated['image'] = $path;
        }
        $event = Event::create($validated);
        return $this->sendResponse($event, 'Event created', 201);
    }

    public function show(Event $event)
    {
        $event->load('creator');
        return $this->sendResponse($event, 'Event retrieved');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:academic,cultural,sports,meeting,holiday,field_trip',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'venue' => 'nullable|string',
            'audience' => 'sometimes|in:all,students,teachers,staff',
            'participants' => 'nullable|array',
            'image' => 'nullable|image',
        ]);
        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $path = $request->file('image')->store('events', 'public');
            $validated['image'] = $path;
        }
        $event->update($validated);
        return $this->sendResponse($event, 'Event updated');
    }

    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();
        return $this->sendResponse([], 'Event deleted');
    }
}