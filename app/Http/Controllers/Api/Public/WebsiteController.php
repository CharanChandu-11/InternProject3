<?php
// app/Http/Controllers/Api/Public/WebsiteController.php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolSetting;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\ContactQuery;
use App\Models\AdmissionInquiry;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\GalleryResource;
use Illuminate\Http\Request;

class WebsiteController extends BaseController
{
    /**
     * Get school information
     */
    public function schoolInfo()
    {
        $school = SchoolSetting::first();
        
        return $this->sendResponse([
            'name' => $school->school_name ?? 'Smart School',
            'address' => $school->address ?? '',
            'phone' => $school->phone ?? '',
            'email' => $school->email ?? '',
            'website' => $school->website ?? '',
            'logo' => $school->logo ? asset('storage/'.$school->logo) : null,
            'established_year' => $school->established_year ?? 2000,
            'principal_name' => $school->principal_name ?? '',
            'mission' => $school->mission_statement ?? '',
            'vision' => $school->vision_statement ?? ''
        ], 'School information retrieved successfully');
    }

    /**
     * Get public announcements
     */
    public function announcements(Request $request)
    {
        $announcements = Announcement::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            })
            ->latest()
            ->paginate($request->per_page ?? 10);

        return $this->sendPaginatedResponse(
            AnnouncementResource::collection($announcements),
            'Announcements retrieved successfully'
        );
    }

    /**
     * Get public events
     */
    public function events(Request $request)
    {
        $query = Event::where('start_date', '>=', now());

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->orderBy('start_date')
            ->paginate($request->per_page ?? 10);

        return $this->sendPaginatedResponse(
            EventResource::collection($events),
            'Events retrieved successfully'
        );
    }

    /**
     * Get public gallery
     */
    public function gallery(Request $request)
    {
        $query = Gallery::where('is_active', true);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $images = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 24);

        return $this->sendPaginatedResponse(
            GalleryResource::collection($images),
            'Gallery images retrieved successfully'
        );
    }

    /**
     * Submit contact form
     */
    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $contact = ContactQuery::create($request->all());

        // Send email notification
        // Mail::to($school->email)->send(new ContactQueryMail($contact));

        return $this->sendResponse([
            'id' => $contact->id
        ], 'Thank you for contacting us. We will get back to you soon.');
    }

    /**
     * Submit admission inquiry
     */
    public function admissionInquiry(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'student_dob' => 'required|date',
            'student_gender' => 'required|in:male,female,other',
            'class_applying_for' => 'required|string',
            'parent_name' => 'required|string|max:255',
            'parent_email' => 'required|email|max:255',
            'parent_phone' => 'required|string|max:20',
            'address' => 'required|string'
        ]);

        $inquiry = AdmissionInquiry::create($request->all());

        return $this->sendResponse([
            'id' => $inquiry->id,
            'inquiry_number' => $inquiry->inquiry_number
        ], 'Admission inquiry submitted successfully. We will contact you soon.');
    }
}