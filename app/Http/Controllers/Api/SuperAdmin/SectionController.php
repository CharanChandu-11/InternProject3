<?php
// app/Http/Controllers/Api/SuperAdmin/SectionController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends BaseController
{
    public function index()
    {
        $sections = Section::with('class')->get();
        return $this->sendResponse($sections, 'Sections retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'capacity' => 'nullable|integer',
        ]);

        $section = Section::create($validated);
        return $this->sendResponse($section, 'Section created', 201);
    }

    public function show(Section $section)
    {
        $section->load('class');
        return $this->sendResponse($section, 'Section retrieved');
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'class_id' => 'sometimes|exists:classes,id',
            'capacity' => 'nullable|integer',
        ]);

        $section->update($validated);
        return $this->sendResponse($section, 'Section updated');
    }

    public function destroy(Section $section)
    {
        $section->delete();
        return $this->sendResponse([], 'Section deleted');
    }
}