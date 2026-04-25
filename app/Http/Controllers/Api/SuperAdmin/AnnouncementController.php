<?php
// app/Http/Controllers/Api/SuperAdmin/AnnouncementController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends BaseController
{
    public function index()
    {
        $announcements = Announcement::with('creator')->latest()->get();
        return $this->sendResponse($announcements, 'Announcements retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'audience' => 'required|in:all,students,parents,teachers,employees,specific_classes',
            'specific_classes' => 'nullable|array',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_published' => 'boolean',
        ]);
        $validated['created_by'] = auth()->id();
        $validated['attachments'] = []; // handle file uploads if needed
        $announcement = Announcement::create($validated);
        return $this->sendResponse($announcement, 'Announcement created', 201);
    }

    public function show(Announcement $announcement)
    {
        $announcement->load('creator');
        return $this->sendResponse($announcement, 'Announcement retrieved');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'sometimes|string',
            'audience' => 'sometimes|in:all,students,parents,teachers,employees,specific_classes',
            'specific_classes' => 'nullable|array',
            'publish_date' => 'sometimes|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_published' => 'boolean',
        ]);
        $announcement->update($validated);
        return $this->sendResponse($announcement, 'Announcement updated');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return $this->sendResponse([], 'Announcement deleted');
    }
}