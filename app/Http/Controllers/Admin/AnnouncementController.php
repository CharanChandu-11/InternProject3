<?php
// app/Http/Controllers/Admin/AnnouncementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements
     */
    public function index(Request $request)
    {
        $query = Announcement::with('creator');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status == 'published') {
                $query->where('is_published', true)
                      ->where('publish_date', '<=', now())
                      ->where(function($q) {
                          $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>=', now());
                      });
            } elseif ($request->status == 'draft') {
                $query->where('is_published', false);
            } elseif ($request->status == 'scheduled') {
                $query->where('is_published', true)
                      ->where('publish_date', '>', now());
            } elseif ($request->status == 'expired') {
                $query->where('is_published', true)
                      ->whereNotNull('expiry_date')
                      ->where('expiry_date', '<', now());
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $announcements = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement
     */
    public function create()
    {
        $classes = Classes::with('sections')->get();
        return view('admin.announcements.create', compact('classes'));
    }

    /**
     * Store a newly created announcement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience' => 'required|in:all,students,parents,teachers,employees,specific_classes',
            'specific_classes' => 'required_if:audience,specific_classes|array|nullable',
            'specific_classes.*' => 'exists:classes,id',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_published' => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audience' => $validated['audience'],
            'specific_classes' => $validated['audience'] == 'specific_classes' ? $validated['specific_classes'] : null,
            'publish_date' => $validated['publish_date'],
            'expiry_date' => $validated['expiry_date'],
            'is_published' => $request->has('is_published'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified announcement
     */
    public function show(Announcement $announcement)
    {
        $announcement->load('creator');
        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement
     */
    public function edit(Announcement $announcement)
    {
        $classes = Classes::with('sections')->get();
        return view('admin.announcements.edit', compact('announcement', 'classes'));
    }

    /**
     * Update the specified announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience' => 'required|in:all,students,parents,teachers,employees,specific_classes',
            'specific_classes' => 'required_if:audience,specific_classes|array|nullable',
            'specific_classes.*' => 'exists:classes,id',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_published' => 'nullable|boolean',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audience' => $validated['audience'],
            'specific_classes' => $validated['audience'] == 'specific_classes' ? $validated['specific_classes'] : null,
            'publish_date' => $validated['publish_date'],
            'expiry_date' => $validated['expiry_date'],
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Toggle publish status
     */
    public function togglePublish(Announcement $announcement)
    {
        $announcement->update(['is_published' => !$announcement->is_published]);
        $status = $announcement->is_published ? 'published' : 'unpublished';
        return redirect()->back()->with('success', "Announcement {$status} successfully.");
    }

    /**
     * Remove the specified announcement
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}