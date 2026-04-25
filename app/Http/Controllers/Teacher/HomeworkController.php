<?php
// app/Http/Controllers/Teacher/HomeworkController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class HomeworkController extends Controller
{
    /**
     * Display a listing of homework
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = Homework::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject']);
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('submission_date', '>=', Carbon::today())
                      ->where('status', 'active');
            } elseif ($request->status == 'expired') {
                $query->where('submission_date', '<', Carbon::today())
                      ->orWhere('status', 'expired');
            } elseif ($request->status == 'draft') {
                $query->where('status', 'draft');
            }
        }
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        $homeworks = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());
        
        // Get classes for filter
        $classes = Timetable::where('teacher_id', $teacher->id)
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id')
            ->values();
        
        // Get subjects for filter
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();
        
        return view('teacher.homework.index', compact('homeworks', 'classes', 'subjects'));
    }
    
    /**
     * Show form for creating new homework
     */
    public function create()
    {
        $teacher = Auth::user();
        
        // Get unique class-section combinations
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id;
            })
            ->map(function($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name,
                    'section_id' => $item->section_id,
                    'section_name' => $item->section->name,
                ];
            })
            ->values();
        
        // Get subjects taught by teacher
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();
        
        return view('teacher.homework.create', compact('classSections', 'subjects'));
    }
    
    /**
     * Store a newly created homework
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'submission_date' => 'required|date|after_or_equal:today',
            'total_marks' => 'nullable|integer|min:0',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,zip',
            'status' => 'nullable|in:active,draft',
        ]);
        
        $teacher = Auth::user();
        
        // Verify teacher teaches this subject and class
        $teachesSubject = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();
        
        if (!$teachesSubject) {
            return redirect()->back()->with('error', 'You are not authorized to assign homework for this subject/class.')
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('homework', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
            
            $homework = Homework::create([
                'title' => $request->title,
                'description' => $request->description,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id,
                'submission_date' => $request->submission_date,
                'submission_time' => Carbon::now(),
                'total_marks' => $request->total_marks,
                'attachments' => json_encode($attachments),
                'status' => $request->status ?? 'active',
            ]);
            
            DB::commit();
            
            // Notify students if homework is active
            if ($homework->status == 'active') {
                $this->notifyStudents($homework);
            }
            
            return redirect()->route('teacher.homework.index')
                ->with('success', 'Homework created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create homework: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified homework
     */
    public function show(Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $homework->load(['class', 'section', 'subject', 'teacher']);
        
        // Get submission statistics
        $studentsCount = Student::where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->count();
        
        $submissionsCount = $homework->submissions()->count();
        $submittedCount = $homework->submissions()->where('status', 'submitted')->count();
        $gradedCount = $homework->submissions()->where('status', 'graded')->count();
        $lateCount = $homework->submissions()->where('status', 'late')->count();
        
        $stats = [
            'total_students' => $studentsCount,
            'total_submissions' => $submissionsCount,
            'submitted' => $submittedCount,
            'graded' => $gradedCount,
            'late' => $lateCount,
            'not_submitted' => $studentsCount - $submissionsCount,
            'submission_rate' => $studentsCount > 0 ? round(($submissionsCount / $studentsCount) * 100, 2) : 0,
        ];
        
        return view('teacher.homework.show', compact('homework', 'stats'));
    }
    
    /**
     * Show form for editing homework
     */
    public function edit(Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $classSections = Timetable::where('teacher_id', $teacher->id)
            ->with(['class', 'section'])
            ->get()
            ->unique(function($item) {
                return $item->class_id;
            })
            ->map(function($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name,
                    'section_id' => $item->section_id,
                    'section_name' => $item->section->name,
                ];
            })
            ->values();
        
        $subjects = ClassSubject::where('teacher_id', $teacher->id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();
        
        return view('teacher.homework.edit', compact('homework', 'classSections', 'subjects'));
    }
    
    /**
     * Update the specified homework
     */
    public function update(Request $request, Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'submission_date' => 'required|date|after_or_equal:today',
            'total_marks' => 'nullable|integer|min:0',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,zip',
            'status' => 'nullable|in:active,draft,expired',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Handle new attachments
            $attachments = json_decode($homework->attachments, true) ?? [];
            
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('homework', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
            
            // Remove attachments if requested
            if ($request->has('remove_attachments')) {
                $removeIds = $request->remove_attachments;
                foreach ($removeIds as $index) {
                    if (isset($attachments[$index])) {
                        Storage::disk('public')->delete($attachments[$index]['path']);
                        unset($attachments[$index]);
                    }
                }
                $attachments = array_values($attachments);
            }
            
            $homework->update([
                'title' => $request->title,
                'description' => $request->description,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'subject_id' => $request->subject_id,
                'submission_date' => $request->submission_date,
                'total_marks' => $request->total_marks,
                'attachments' => json_encode($attachments),
                'status' => $request->status ?? 'active',
            ]);
            
            DB::commit();
            
            return redirect()->route('teacher.homework.index')
                ->with('success', 'Homework updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update homework: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified homework
     */
    public function destroy(Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Delete attachments
        $attachments = json_decode($homework->attachments, true) ?? [];
        foreach ($attachments as $attachment) {
            Storage::disk('public')->delete($attachment['path']);
        }
        
        // Delete submissions
        foreach ($homework->submissions as $submission) {
            if ($submission->attachments) {
                foreach (json_decode($submission->attachments, true) ?? [] as $attachment) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
            $submission->delete();
        }
        
        $homework->delete();
        
        return redirect()->route('teacher.homework.index')
            ->with('success', 'Homework deleted successfully.');
    }
    
    /**
     * Display homework submissions
     */
    public function submissions(Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get all students in the class
        $students = Student::where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        // Get submissions
        $submissions = $homework->submissions()
            ->get()
            ->keyBy('student_id');
        
        $submissionData = [];
        foreach ($students as $student) {
            $submission = $submissions[$student->id] ?? null;
            $submissionData[] = [
                'student' => $student,
                'submission' => $submission,
                'is_submitted' => !is_null($submission),
                'is_late' => $submission ? $submission->is_late : false,
                'is_graded' => $submission && $submission->status == 'graded',
            ];
        }
        
        $stats = [
            'total_students' => $students->count(),
            'submitted' => collect($submissionData)->where('is_submitted', true)->count(),
            'not_submitted' => collect($submissionData)->where('is_submitted', false)->count(),
            'late' => collect($submissionData)->where('is_late', true)->count(),
            'graded' => collect($submissionData)->where('is_graded', true)->count(),
            'submission_rate' => $students->count() > 0 
                ? round((collect($submissionData)->where('is_submitted', true)->count() / $students->count()) * 100, 2) 
                : 0,
        ];
        
        return view('teacher.homework.submissions', compact('homework', 'submissionData', 'stats'));
    }
    
    /**
     * Grade a homework submission
     */
    public function grade(Request $request, Homework $homework, HomeworkSubmission $submission)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($submission->homework_id != $homework->id) {
            return redirect()->back()->with('error', 'Submission does not belong to this homework.');
        }
        
        $request->validate([
            'marks' => 'required|integer|min:0|max:' . ($homework->total_marks ?? 100),
            'feedback' => 'nullable|string|max:1000',
        ]);
        
        $submission->update([
            'obtained_marks' => $request->marks,
            'feedback' => $request->feedback,
            'status' => 'graded',
        ]);
        
        // Calculate grade percentage
        $percentage = $homework->total_marks > 0 
            ? round(($request->marks / $homework->total_marks) * 100, 2) 
            : 0;
        
        // Notify student
        Notification::create([
            'user_id' => $submission->student->user_id,
            'title' => 'Homework Graded',
            'message' => "Your homework '{$homework->title}' has been graded. You received {$request->marks}/{$homework->total_marks} marks ({$percentage}%).",
            'type' => 'homework',
            'priority' => 'medium',
            'data' => [
                'homework_id' => $homework->id,
                'homework_title' => $homework->title,
                'marks' => $request->marks,
                'total_marks' => $homework->total_marks,
                'percentage' => $percentage,
            ],
        ]);
        
        return redirect()->route('teacher.homework.submissions', $homework)
            ->with('success', 'Submission graded successfully.');
    }
    
    /**
     * Download attachment
     */
    public function downloadAttachment($homeworkId, $attachmentIndex)
    {
        $homework = Homework::findOrFail($homeworkId);
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $attachments = json_decode($homework->attachments, true) ?? [];
        
        if (!isset($attachments[$attachmentIndex])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$attachmentIndex];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }
        
        return response()->download($filePath, $attachment['name']);
    }
    
    /**
     * Download submission attachment
     */
    public function downloadSubmissionAttachment($submissionId, $attachmentIndex)
    {
        $submission = HomeworkSubmission::findOrFail($submissionId);
        $homework = $submission->homework;
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $attachments = json_decode($submission->attachments, true) ?? [];
        
        if (!isset($attachments[$attachmentIndex])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$attachmentIndex];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }
        
        return response()->download($filePath, $attachment['name']);
    }
    
    /**
     * Notify students about new homework
     */
    private function notifyStudents($homework)
    {
        $students = Student::where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->get();
        
        foreach ($students as $student) {
            Notification::create([
                'user_id' => $student->user_id,
                'title' => 'New Homework Assigned',
                'message' => "New homework '{$homework->title}' has been assigned in {$homework->subject->name}. Submission due by " . $homework->submission_date->format('F j, Y'),
                'type' => 'homework',
                'priority' => 'high',
                'data' => [
                    'homework_id' => $homework->id,
                    'homework_title' => $homework->title,
                    'subject' => $homework->subject->name,
                    'submission_date' => $homework->submission_date->toDateString(),
                ],
            ]);
        }
    }
}