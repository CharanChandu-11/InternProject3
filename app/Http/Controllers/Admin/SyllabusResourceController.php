<?php
// app/Http/Controllers/Admin/SyllabusResourceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyllabusTopic;
use App\Models\SyllabusResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SyllabusResourceController extends Controller
{
    public function store(Request $request, SyllabusTopic $topic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:book,article,video,website,document,other',
            'url' => 'nullable|url',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,mp4',
            'description' => 'nullable|string',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('syllabus-resources', 'public');
        }

        $topic->resources()->create([
            'title' => $request->title,
            'type' => $request->type,
            'url' => $request->url,
            'file_path' => $filePath,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Resource added successfully.');
    }

    public function destroy(SyllabusResource $resource)
    {
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }
        $resource->delete();

        return redirect()->back()->with('success', 'Resource deleted successfully.');
    }
}