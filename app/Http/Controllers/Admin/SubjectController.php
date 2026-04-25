<?php
// app/Http/Controllers/Admin/SubjectController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\ClassSubject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request)
    {
        $query = Subject::query();

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        // Type filter
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        $subjects = $query->orderBy('name')->paginate(15);
        
        // Get all classes for assignment modal
        $classes = Classes::with('sections')->get();

        return view('admin.subjects.index', compact('subjects', 'classes'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        return view('admin.subjects.create');
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'type' => 'required|in:core,elective,language,practical',
            'description' => 'nullable|string',
        ]);

        Subject::create($request->all());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        $subject->load(['classes' => function($q) {
            $q->withPivot('teacher_id', 'theory_marks', 'practical_marks');
        }]);
        
        return view('admin.subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'type' => 'required|in:core,elective,language,practical',
            'description' => 'nullable|string',
        ]);

        $subject->update($request->all());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        // Check if subject is assigned to any class
        if ($subject->classes()->count() > 0) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'Cannot delete subject. It is assigned to one or more classes.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Assign subject to a class with teacher.
     */
    public function assignToClass(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:users,id',
            'theory_marks' => 'nullable|integer|min:0|max:100',
            'practical_marks' => 'nullable|integer|min:0|max:100',
            'is_lab_required' => 'nullable|boolean',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        
        // Check if already assigned
        if ($subject->classes()->where('class_id', $request->class_id)->exists()) {
            return redirect()->back()
                ->with('error', 'Subject is already assigned to this class.');
        }

        $subject->classes()->attach($request->class_id, [
            'teacher_id' => $request->teacher_id,
            'theory_marks' => $request->theory_marks ?? 100,
            'practical_marks' => $request->practical_marks ?? 0,
            'is_lab_required' => $request->is_lab_required ?? false,
        ]);

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Subject assigned to class successfully.');
    }

    /**
     * Remove subject from class.
     */
    public function removeFromClass(Request $request, Subject $subject)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $subject->classes()->detach($request->class_id);

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Subject removed from class successfully.');
    }
}