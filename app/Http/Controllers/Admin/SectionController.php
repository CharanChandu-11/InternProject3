<?php
// app/Http/Controllers/Admin/SectionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Timetable;
use App\Models\Classes;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of sections.
     */
    public function index(Request $request)
    {
        $query = Section::with(['class', 'class.academicYear']);
        
        // Filter by class
        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        // Search by section name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('class', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $sections = $query->orderBy('class_id')->orderBy('name')->paginate(50);
        $classes = Classes::with('academicYear')->get();
        
        return view('admin.sections.index', compact('sections', 'classes'));
    }
    
    /**
     * Show the form for creating a new section.
     */
    public function create()
    {
        $classes = Classes::with('academicYear')->get();
        return view('admin.sections.create', compact('classes'));
    }
    
    /**
     * Store a newly created section in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:10',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'nullable|integer|min:1',
        ]);
        
        // Check if section already exists in this class
        $exists = Section::where('class_id', $request->class_id)
            ->where('name', $request->name)
            ->exists();
        
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Section ' . $request->name . ' already exists in this class.');
        }
        
        Section::create($request->all());
        
        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }
    
    /**
     * Display the specified section.
     */
    public function show(Section $section)
    {
        $section->load(['class', 'class.academicYear', 'students.user']);
        return view('admin.sections.show', compact('section'));
    }
    
    /**
     * Show the form for editing the specified section.
     */
    public function edit(Section $section)
    {
        $classes = Classes::with('academicYear')->get();
        return view('admin.sections.edit', compact('section', 'classes'));
    }
    
    /**
     * Update the specified section in storage.
     */
    public function update(Request $request, Section $section)
    {
        $request->validate([
            'name' => 'required|string|max:10',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'nullable|integer|min:1',
        ]);
        
        // Check if section already exists in this class (excluding current)
        $exists = Section::where('class_id', $request->class_id)
            ->where('name', $request->name)
            ->where('id', '!=', $section->id)
            ->exists();
        
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Section ' . $request->name . ' already exists in this class.');
        }
        
        $section->update($request->all());
        
        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated successfully.');
    }
    
    /**
     * Remove the specified section from storage.
     */
    public function destroy(Section $section)
    {
        // Check students
        $studentCount = $section->students()->count();

        // Check timetables
        $timetableCount = $section->timetables()->count();

        if ($studentCount > 0 || $timetableCount > 0) {
            return redirect()->back()->with('error',
                "Cannot delete section. It has {$studentCount} student(s) and {$timetableCount} timetable(s)."
            );
        }

        $section->delete();

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section deleted successfully.');
    }
    
    /**
     * Get sections by class (AJAX)
     */
    public function byClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id'
        ]);
        
        $sections = Section::where('class_id', $request->class_id)
            ->orderBy('name')
            ->get(['id', 'name', 'capacity']);
        
        return response()->json($sections);
    }

    public function getSectionsByClass(Classes $class)
    {
        return response()->json($class->sections);
    }
}