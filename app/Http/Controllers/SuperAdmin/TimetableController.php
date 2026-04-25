<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimetableExport;
use ZipStream\Time;

class TimetableController extends Controller
{
    /**
     * Display list of timetables (grouped by class/section)
     */
    public function index(Request $request)
    {
        $classes = Classes::with('sections')->get();
        $selectedClass = $request->class_id ? Classes::find($request->class_id) : null;
        $selectedSection = $request->section_id ? Section::find($request->section_id) : null;
        
        $timetable = [];
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        if ($selectedClass && $selectedSection) {
            $entries = Timetable::where('class_id', $selectedClass->id)
                ->where('section_id', $selectedSection->id)
                ->with(['subject', 'teacher', 'timeSlot'])
                ->get();
                
            foreach ($entries as $entry) {
                $timetable[$entry->day_of_week][$entry->time_slot_id] = $entry;
            }
        }
        
        return view('super-admin.timetable.index', compact('classes', 'selectedClass', 'selectedSection', 'timetable', 'timeSlots', 'days'));
    }
    
    /**
     * Show form for creating a single entry
     */
    public function create(Request $request)
    {
        $classes = Classes::all();
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        $subjects = Subject::all();
        $teachers = User::where('user_type', 'teacher')->get();
        
        return view('super-admin.timetable.create', compact('classes', 'timeSlots', 'subjects', 'teachers'));
    }
    
    /**
     * Store a single entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'day_of_week' => 'required|array',
            'day_of_week.*' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'time_slot_id' => 'required|exists:time_slots,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'room_number' => 'nullable|string|max:50',
        ]);

        foreach ($request->day_of_week as $day) {

            // ✅ Teacher conflict check
            $teacherBusy = Timetable::where('teacher_id', $request->teacher_id)
                ->where('day_of_week', $day)
                ->where('time_slot_id', $request->time_slot_id)
                ->exists();

            if ($teacherBusy) {
                return redirect()->back()
                    ->with('error', 'Teacher not available on ' . ucfirst($day))
                    ->withInput();
            }

            // ✅ Check existing class slot
            $exists = Timetable::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('day_of_week', $day)
                ->where('time_slot_id', $request->time_slot_id)
                ->first();

            if ($exists) {

                // ✅ Only update necessary fields
                $exists->update([
                    'subject_id'   => $request->subject_id,
                    'teacher_id'   => $request->teacher_id,
                    'room_number'  => $request->room_number,
                ]);

            } else {

                Timetable::create([
                    'class_id'     => $request->class_id,
                    'section_id'   => $request->section_id,
                    'day_of_week'  => $day,
                    'time_slot_id' => $request->time_slot_id,
                    'subject_id'   => $request->subject_id,
                    'teacher_id'   => $request->teacher_id,
                    'room_number'  => $request->room_number,
                ]);
            }
        }
        
        return redirect()->route('super-admin.timetable.index', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id
        ])->with('success', 'Timetable entry added successfully.');
    }
    
    /**
     * Show edit form for single entry
     */
    public function edit(Timetable $timetable)
    {
        $classes = Classes::all();
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        $subjects = Subject::all();
        $teachers = User::where('user_type', 'teacher')->get();
        
        return view('super-admin.timetable.edit', compact('timetable', 'classes', 'timeSlots', 'subjects', 'teachers'));
    }
    
    /**
     * Update a single entry
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'day_of_week' => 'required|array',
            'day_of_week.*' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'time_slot_id' => 'required|exists:time_slots,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'room_number' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->day_of_week as $day) {

                // ✅ Teacher availability check (ignore same class/section)
                $teacherBusy = Timetable::where('teacher_id', $request->teacher_id)
                    ->where('day_of_week', $day)
                    ->where('time_slot_id', $request->time_slot_id)
                    ->where(function ($q) use ($request, $day) {
                        $q->where('class_id', '!=', $request->class_id)
                        ->orWhere('section_id', '!=', $request->section_id);
                    })
                    ->exists();

                if ($teacherBusy) {
                    throw new \Exception('Teacher not available on ' . ucfirst($day));
                }

                // ✅ Find existing timetable slot
                $existing = Timetable::where('class_id', $request->class_id)
                    ->where('section_id', $request->section_id)
                    ->where('day_of_week', $day)
                    ->where('time_slot_id', $request->time_slot_id)
                    ->first();

                if ($existing) {

                    // ✅ Update only required fields
                    $existing->update([
                        'subject_id'   => $request->subject_id,
                        'teacher_id'   => $request->teacher_id,
                        'room_number'  => $request->room_number,
                    ]);

                } else {

                    // ✅ Create new entry
                    Timetable::create([
                        'class_id'     => $request->class_id,
                        'section_id'   => $request->section_id,
                        'day_of_week'  => $day,
                        'time_slot_id' => $request->time_slot_id,
                        'subject_id'   => $request->subject_id,
                        'teacher_id'   => $request->teacher_id,
                        'room_number'  => $request->room_number,
                    ]);
                }
            }
        });

        return redirect()->route('super-admin.timetable.index', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id
        ])->with('success', 'Timetable updated successfully.');
    }
    
    /**
     * Delete a single entry
     */
    public function destroy(Timetable $timetable)
    {
        $classId = $timetable->class_id;
        $sectionId = $timetable->section_id;
        $timetable->delete();
        
        return redirect()->route('super-admin.timetable.index', [
            'class_id' => $classId,
            'section_id' => $sectionId
        ])->with('success', 'Entry deleted.');
    }
    
    /**
     * Show grid editor for all days/time slots for a class/section
     */
    public function editGrid(Classes $class, Section $section)
    {
        $timeSlots = TimeSlot::orderBy('start_time')->get();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $subjects = Subject::all();
        $teachers = User::where('user_type', 'teacher')->get();
        
        // Load existing entries
        $entries = Timetable::where('class_id', $class->id)
            ->where('section_id', $section->id)
            ->get()
            ->keyBy(function($item) {
                return $item->day_of_week . '_' . $item->time_slot_id;
            });
        
        return view('super-admin.timetable.edit-grid', compact('class', 'section', 'timeSlots', 'days', 'subjects', 'teachers', 'entries'));
    }
    
    /**
     * Update grid data
     */
    // public function updateGrid(Request $request, Classes $class, Section $section)
    // {
    //     // Filter out empty entries (where both subject_id and teacher_id are empty)
    //     $entries = collect($request->entries)->filter(function ($entry) {
    //         return !empty($entry['subject_id']) || !empty($entry['teacher_id']);
    //     })->values()->toArray();

    //     // If no valid entries, simply delete all and redirect
    //     if (empty($entries)) {
    //         Timetable::where('class_id', $class->id)
    //             ->where('section_id', $section->id)
    //             ->delete();
    //         return redirect()->route('super-admin.timetable.index', [
    //             'class_id' => $class->id,
    //             'section_id' => $section->id
    //         ])->with('success', 'Timetable cleared successfully.');
    //     }

    //     $request->merge(['entries' => $entries]);

    //     // Validate only the kept entries
    //     $request->validate([
    //         'entries' => 'required|array',
    //         'entries.*.day' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday',
    //         'entries.*.time_slot_id' => 'required|exists:time_slots,id',
    //         'entries.*.subject_id' => 'required|exists:subjects,id',
    //         'entries.*.teacher_id' => 'required|exists:users,id',
    //         'entries.*.room_number' => 'nullable|string|max:50',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         // Delete existing entries for this class/section
    //         Timetable::where('class_id', $class->id)
    //             ->where('section_id', $section->id)
    //             ->delete();

    //         // Insert new entries
    //         foreach ($entries as $entry) {
    //             Timetable::create([
    //                 'class_id' => $class->id,
    //                 'section_id' => $section->id,
    //                 'day_of_week' => $entry['day'],
    //                 'time_slot_id' => $entry['time_slot_id'],
    //                 'subject_id' => $entry['subject_id'],
    //                 'teacher_id' => $entry['teacher_id'],
    //                 'room_number' => $entry['room_number'] ?? null,
    //             ]);
    //         }

    //         DB::commit();

    //         return redirect()->route('super-admin.timetable.index', [
    //             'class_id' => $class->id,
    //             'section_id' => $section->id
    //         ])->with('success', 'Timetable updated successfully.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->with('error', 'Failed to update timetable: ' . $e->getMessage());
    //     }
    // }
    
    /**
     * Export timetable to Excel
     */
    // public function export(Classes $class, Section $section)
    // {
    //     return Excel::download(new TimetableExport($class, $section), 'timetable_' . $class->name . '_' . $section->name . '.xlsx');
    // }
}