<?php
// app/Http/Controllers/Student/HomeworkController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeworkController extends Controller
{
    /**
     * Display a listing of homework for the student
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('status', 'active')
            ->with(['subject', 'teacher']);
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'pending') {
                $query->where('submission_date', '>=', Carbon::today());
            } elseif ($request->status == 'overdue') {
                $query->where('submission_date', '<', Carbon::today());
            } elseif ($request->status == 'submitted') {
                $submittedIds = HomeworkSubmission::where('student_id', $student->id)
                    ->pluck('homework_id');
                $query->whereIn('id', $submittedIds);
            }
        }
        
        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        $homeworks = $query->orderBy('submission_date')
            ->paginate(10)
            ->appends($request->query());
        
        // Get submissions for these homeworks
        $submissions = HomeworkSubmission::where('student_id', $student->id)
            ->whereIn('homework_id', $homeworks->pluck('id'))
            ->get()
            ->keyBy('homework_id');
        
        // Get subjects for filter
        $subjects = Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();
        
        // Statistics
        $stats = [
            'total' => Homework::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->where('status', 'active')
                ->count(),
            'pending' => Homework::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->where('status', 'active')
                ->where('submission_date', '>=', Carbon::today())
                ->count(),
            'overdue' => Homework::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->where('status', 'active')
                ->where('submission_date', '<', Carbon::today())
                ->whereDoesntHave('submissions', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->count(),
            'submitted' => HomeworkSubmission::where('student_id', $student->id)->count(),
            'graded' => HomeworkSubmission::where('student_id', $student->id)
                ->where('status', 'graded')
                ->count(),
        ];
        
        return view('student.homework.index', compact('homeworks', 'submissions', 'subjects', 'stats'));
    }
    
    /**
     * Display the specified homework
     */
    public function show(Homework $homework)
    {
        $student = Auth::user()->student;
        
        // Verify homework belongs to student's class
        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            abort(404, 'Homework not found.');
        }
        
        $homework->load(['subject', 'teacher', 'class', 'section']);
        
        // Get student's submission if exists
        $submission = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();
        
        $isOverdue = $homework->submission_date < Carbon::today() && !$submission;
        $canSubmit = (!$submission && !$isOverdue) || ($submission && $submission->status == 'graded');
        $daysRemaining = Carbon::today()->diffInDays($homework->submission_date, false);
        
        // Get attachments
        $attachments = json_decode($homework->attachments, true) ?? [];
        
        return view('student.homework.show', compact('homework', 'submission', 'isOverdue', 'canSubmit', 'daysRemaining', 'attachments'));
    }
    
    /**
     * Submit homework
     */
    public function submit(Request $request, Homework $homework)
    {
        $student = Auth::user()->student;
        
        // Verify homework belongs to student's class
        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            return response()->json(['error' => 'Homework not found.'], 404);
        }
        
        // Check if already submitted
        $existingSubmission = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();
        
        if ($existingSubmission && $existingSubmission->status != 'graded') {
            return redirect()->back()->with('error', 'You have already submitted this homework.');
        }
        
        $request->validate([
            'submission_text' => 'required_without:attachments|nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,zip',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Handle file attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('homework-submissions', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
            
            $isLate = $homework->submission_date < Carbon::today();
            
            $submission = HomeworkSubmission::updateOrCreate(
                [
                    'homework_id' => $homework->id,
                    'student_id' => $student->id,
                ],
                [
                    'submission_text' => $request->submission_text,
                    'attachments' => json_encode($attachments),
                    'submitted_at' => now(),
                    'status' => $isLate ? 'late' : 'submitted',
                ]
            );
            
            DB::commit();
            
            // Notify teacher
            // \App\Models\Notification::create([
            //     'title' => 'New Homework Submission',
            //     'message' => "Student {$student->full_name} has submitted homework for {$homework->title}",
            //     'type' => 'in_app',
            //     'recipients' => [$homework->teacher_id],
            //     'priority' => 'medium',
            //     'send_to' => [$homework->teacher_id],
            //     'is_read' => false,
            //     'created_by' => $student->user_id,
            //     'data' => [
            //         'homework_id' => $homework->id,
            //         'student_id' => $student->id,
            //         'student_name' => $student->full_name,
            //     ],
            // ]);
            
            return redirect()->route('student.homework.show', $homework)
                ->with('success', 'Homework submitted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit homework: ' . $e->getMessage());
        }
    }
    
    /**
     * Download homework attachment
     */
    public function downloadAttachment($homeworkId, $attachmentIndex)
    {
        $homework = Homework::findOrFail($homeworkId);
        $student = Auth::user()->student;
        
        // Verify homework belongs to student's class
        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            abort(404);
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
        $student = Auth::user()->student;
        
        // Verify submission belongs to student
        if ($submission->student_id != $student->id) {
            abort(404);
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
}