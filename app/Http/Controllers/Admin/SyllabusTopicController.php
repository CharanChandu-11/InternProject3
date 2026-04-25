<?php
// app/Http/Controllers/Admin/SyllabusTopicController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\SyllabusTopic;
use Illuminate\Http\Request;

class SyllabusTopicController extends Controller
{
    public function store(Request $request, Syllabus $syllabus)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'week_number' => 'nullable|integer',
            'session_count' => 'nullable|integer',
            'learning_objectives' => 'nullable|string',
            'teaching_methods' => 'nullable|string',
            'assessment_methods' => 'nullable|string',
        ]);

        $maxOrder = $syllabus->topics()->max('sort_order') ?? 0;

        $topic = $syllabus->topics()->create([
            'title' => $request->title,
            'description' => $request->description,
            'week_number' => $request->week_number,
            'session_count' => $request->session_count ?? 1,
            'learning_objectives' => $request->learning_objectives,
            'teaching_methods' => $request->teaching_methods,
            'assessment_methods' => $request->assessment_methods,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', 'Topic added successfully.');
    }

    public function update(Request $request, SyllabusTopic $topic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'week_number' => 'nullable|integer',
            'session_count' => 'nullable|integer',
        ]);

        $topic->update($request->all());

        return redirect()->back()->with('success', 'Topic updated successfully.');
    }

    public function destroy(SyllabusTopic $topic)
    {
        $topic->delete();
        return redirect()->back()->with('success', 'Topic deleted successfully.');
    }

    public function reorder(Request $request, Syllabus $syllabus)
    {
        $request->validate([
            'topics' => 'required|array',
            'topics.*.id' => 'exists:syllabus_topics,id',
            'topics.*.sort_order' => 'integer',
        ]);

        foreach ($request->topics as $item) {
            SyllabusTopic::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}