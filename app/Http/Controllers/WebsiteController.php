<?php
// app/Http/Controllers/WebsiteController.php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\User;
use App\Models\SchoolSetting;
use App\Models\ContactQuery;
use App\Models\NewsletterSubscriber;
use App\Models\AdmissionInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class WebsiteController extends Controller
{
    protected $school;

    public function __construct()
    {
        $this->school = SchoolSetting::first();
        view()->share('school', $this->school);
    }

    /**
     * Homepage
     */
    public function index()
    {
        $events = Event::where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(3)
            ->get();

        $announcements = Announcement::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            })
            ->latest()
            ->take(5)
            ->get();

        $gallery = Gallery::where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        $stats = [
            'students' => \App\Models\Student::count(),
            'teachers' => User::where('user_type', 'teacher')->count(),
            'classrooms' => \App\Models\Classes::count() * 3, // rough estimate
            'years' => 25,
        ];

        return view('website.index', compact('events', 'announcements', 'gallery', 'stats'));
    }

    /**
     * About Us Page
     */
    public function aboutUs()
    {
        $stats = [
            'students' => \App\Models\Student::count(),
            'teachers' => User::where('user_type', 'teacher')->count(),
            'classrooms' => 50,
            'years_established' => 25
        ];

        $leadership = \App\Models\Employee::whereIn('designation', ['Principal', 'Vice Principal', 'Director'])
            ->orWhere('designation', 'like', '%head%')
            ->with('user')
            ->get();

        return view('website.about', compact('stats', 'leadership'));
    }

    /**
     * Admissions Page
     */
    public function admissions()
    {
        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
        $classes = \App\Models\Classes::with('sections')->get();

        return view('website.admissions', compact('academicYears', 'classes'));
    }

    /**
     * Academics Page
     */
    public function academics()
    {
        return view('website.academics');
    }

    /**
     * Faculty Page
     */
    public function faculty()
    {
        $teachers = User::where('user_type', 'teacher')
            ->with(['profile', 'employee'])
            ->get();

        return view('website.faculty', compact('teachers'));
    }

    /**
     * Gallery Page
     */
    public function gallery(Request $request)
    {
        $categories = Gallery::select('category')
            ->distinct()
            ->get()
            ->pluck('category');

        $images = Gallery::with('uploader')
            ->latest()
            ->paginate(24);

        return view('website.gallery', compact('images', 'categories'));
    }

    /**
     * Events Page
     */
    public function events()
    {
        $upcomingEvents = Event::where('start_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        $pastEvents = Event::where('start_date', '<', now())
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('website.events', compact('upcomingEvents', 'pastEvents'));
    }

    /**
     * News Page
     */
    public function news()
    {
        $announcements = Announcement::where('is_published', true)
            ->where('publish_date', '<=', now())
            ->latest()
            ->paginate(10);

        return view('website.news', compact('announcements'));
    }

    /**
     * Contact Page
     */
    public function contact()
    {
        return view('website.contact');
    }

    /**
     * Submit Contact Form
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        ContactQuery::create($request->all());

        // Optionally send email
        // Mail::to($this->school->email)->send(new ContactQueryMail($request->all()));

        return redirect()->back()->with('success', 'Thank you for contacting us. We will get back to you soon.');
    }

    /**
     * Newsletter Subscribe
     */
    public function newsletterSubscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ]);

        NewsletterSubscriber::create([
            'email' => $request->email,
            'subscribed_at' => now(),
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Subscribed successfully!']);
    }

    /**
     * Admission Inquiry
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
            'address' => 'required|string',
        ]);

        $inquiry = AdmissionInquiry::create($request->all());

        // Optionally send email
        // Mail::to($this->school->email)->send(new AdmissionInquiryMail($inquiry));

        return redirect()->back()->with('success', 'Admission inquiry submitted successfully. We will contact you soon.');
    }
}