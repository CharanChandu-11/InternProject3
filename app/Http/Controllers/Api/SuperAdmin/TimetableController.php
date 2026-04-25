<?php
// app/Http/Controllers/Api/SuperAdmin/TimetableController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimetableController extends BaseController
{
    public function index(Request $request)
    {
        $query = Timetable::with(['class', 'section', 'subject', 'teacher', 'timeSlot']);
        if ($request->has('class_id')) $query->where('class_id', $request->class_id);
        if ($request->has('section_id')) $query->where('section_id', $request->section_id);

        $timetable = $query->get();
        return $this->sendResponse($timetable, 'Timetable retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_slot_id' => 'required|exists:time_slots,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'room_number' => 'nullable|string',
        ]);

        $timetable = Timetable::create($validated);
        return $this->sendResponse($timetable, 'Timetable entry created', 201);
    }

    public function show(Timetable $timetable)
    {
        $timetable->load(['class', 'section', 'subject', 'teacher', 'timeSlot']);
        return $this->sendResponse($timetable, 'Timetable entry retrieved');
    }

    public function update(Request $request, Timetable $timetable)
    {
        $validated = $request->validate([
            'class_id' => 'sometimes|exists:classes,id',
            'section_id' => 'sometimes|exists:sections,id',
            'day_of_week' => 'sometimes|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_slot_id' => 'sometimes|exists:time_slots,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'teacher_id' => 'sometimes|exists:users,id',
            'room_number' => 'nullable|string',
        ]);

        $timetable->update($validated);
        return $this->sendResponse($timetable, 'Timetable entry updated');
    }

    public function destroy(Timetable $timetable)
    {
        $timetable->delete();
        return $this->sendResponse([], 'Timetable entry deleted');
    }

    public function edit($class, $section)
    {
        $timetable = Timetable::where('class_id', $class)
            ->where('section_id', $section)
            ->get()
            ->groupBy('day_of_week');
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        $data = ['timetable' => $timetable, 'timeSlots' => $timeSlots];
        return $this->sendResponse($data, 'Timetable edit data');
    }

    public function updateForClassSection(Request $request, $class, $section)
    {
        // Bulk update: expects array of entries
        $request->validate([
            'entries' => 'required|array',
        ]);

        Timetable::where('class_id', $class)->where('section_id', $section)->delete();

        foreach ($request->entries as $entry) {
            Timetable::create($entry + ['class_id' => $class, 'section_id' => $section]);
        }

        return $this->sendResponse([], 'Timetable updated');
    }
}