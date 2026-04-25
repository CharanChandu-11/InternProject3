<?php
// app/Http/Controllers/Api/SuperAdmin/SubjectController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends BaseController
{
    public function index()
    {
        $subjects = Subject::all();
        return $this->sendResponse($subjects, 'Subjects retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|unique:subjects',
            'type' => 'required|in:core,elective,language,practical',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create($validated);
        return $this->sendResponse($subject, 'Subject created', 201);
    }

    public function show(Subject $subject)
    {
        $subject->load('classes');
        return $this->sendResponse($subject, 'Subject retrieved');
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'code' => 'sometimes|unique:subjects,code,' . $subject->id,
            'type' => 'sometimes|in:core,elective,language,practical',
            'description' => 'nullable|string',
        ]);

        $subject->update($validated);
        return $this->sendResponse($subject, 'Subject updated');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return $this->sendResponse([], 'Subject deleted');
    }
}