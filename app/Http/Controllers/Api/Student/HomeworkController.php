<?php
// app/Http/Controllers/Api/Student/HomeworkController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\HomeworkResource;
use App\Http\Resources\HomeworkSubmissionResource;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class HomeworkController extends BaseController
{
    /**
     * List homework for the student with filters
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;

        $query = Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('status', 'active')
            ->with(['subject', 'teacher']);

        if ($request->filled('status')) {
            if ($request->status == 'pending') {
                $query->where('submission_date', '>=', Carbon::today());
            } elseif ($request->status == 'overdue') {
                $query->where('submission_date', '<', Carbon::today());
            } elseif ($request->status == 'submitted') {
                $submittedIds = HomeworkSubmission::where('student_id', $student->id)->pluck('homework_id');
                $query->whereIn('id', $submittedIds);
            }
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $homeworks = $query->orderBy('submission_date')->paginate($request->per_page ?? 10);

        $submissions = HomeworkSubmission::where('student_id', $student->id)
            ->whereIn('homework_id', $homeworks->pluck('id'))
            ->get()
            ->keyBy('homework_id');

        $subjects = Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();

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
                ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $student->id))
                ->count(),
            'submitted' => HomeworkSubmission::where('student_id', $student->id)->count(),
            'graded' => HomeworkSubmission::where('student_id', $student->id)->where('status', 'graded')->count(),
        ];

        $data = $homeworks->map(function ($homework) use ($submissions) {
            $sub = $submissions[$homework->id] ?? null;
            return [
                'homework' => new HomeworkResource($homework),
                'submission' => $sub ? new HomeworkSubmissionResource($sub) : null,
                'is_submitted' => !is_null($sub),
                'is_late' => $sub && $sub->is_late,
                'is_graded' => $sub && $sub->status == 'graded',
                'days_remaining' => Carbon::today()->diffInDays($homework->submission_date, false),
            ];
        });

        return $this->sendResponse([
            'homeworks' => $data,
            'subjects' => $subjects,
            'statistics' => $stats,
            'pagination' => [
                'current_page' => $homeworks->currentPage(),
                'last_page' => $homeworks->lastPage(),
                'per_page' => $homeworks->perPage(),
                'total' => $homeworks->total(),
            ],
        ], 'Homework list retrieved');
    }

    /**
     * Show a specific homework with submission details
     */
    public function show(Homework $homework)
    {
        $student = Auth::user()->student;

        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            return $this->sendError('Homework not found', [], 404);
        }

        $homework->load(['subject', 'teacher']);
        $submission = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        $isOverdue = $homework->submission_date < Carbon::today() && !$submission;
        $daysRemaining = Carbon::today()->diffInDays($homework->submission_date, false);

        return $this->sendResponse([
            'homework' => new HomeworkResource($homework),
            'submission' => $submission ? new HomeworkSubmissionResource($submission) : null,
            'is_overdue' => $isOverdue,
            'can_submit' => (!$submission && !$isOverdue) || ($submission && $submission->status == 'graded'),
            'days_remaining' => $daysRemaining,
            'attachments' => json_decode($homework->attachments, true) ?? [],
        ], 'Homework details retrieved');
    }

    /**
     * Submit homework (text and/or files)
     */
    public function submit(Request $request, Homework $homework)
    {
        $student = Auth::user()->student;

        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            return $this->sendError('Homework not found', [], 404);
        }

        $existing = HomeworkSubmission::where('homework_id', $homework->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing && $existing->status != 'graded') {
            return $this->sendError('You have already submitted this homework', [], 422);
        }

        $request->validate([
            'submission_text' => 'required_without:attachments|nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,zip',
        ]);

        DB::beginTransaction();
        try {
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('homework-submissions', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }

            $isLate = $homework->submission_date < Carbon::today();
            $submission = HomeworkSubmission::updateOrCreate(
                ['homework_id' => $homework->id, 'student_id' => $student->id],
                [
                    'submission_text' => $request->submission_text,
                    'attachments' => json_encode($attachments),
                    'submitted_at' => now(),
                    'status' => $isLate ? 'late' : 'submitted',
                ]
            );

            DB::commit();

            // Notify teacher (optional)
            // Notification::create(...);

            return $this->sendResponse(new HomeworkSubmissionResource($submission), 'Homework submitted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to submit homework: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Download an attachment from the homework (teacher's uploaded file)
     */
    public function downloadAttachment($homeworkId, $attachmentIndex)
    {
        $homework = Homework::findOrFail($homeworkId);
        $student = Auth::user()->student;

        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $attachments = json_decode($homework->attachments, true) ?? [];
        if (!isset($attachments[$attachmentIndex])) {
            return $this->sendError('Attachment not found', [], 404);
        }

        $file = $attachments[$attachmentIndex];
        $filePath = storage_path('app/public/' . $file['path']);
        if (!file_exists($filePath)) {
            return $this->sendError('File not found', [], 404);
        }

        return response()->download($filePath, $file['name']);
    }

    /**
     * Download an attachment from the student's own submission
     */
    public function downloadSubmissionAttachment($submissionId, $attachmentIndex)
    {
        $submission = HomeworkSubmission::findOrFail($submissionId);
        $student = Auth::user()->student;

        if ($submission->student_id != $student->id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $attachments = json_decode($submission->attachments, true) ?? [];
        if (!isset($attachments[$attachmentIndex])) {
            return $this->sendError('Attachment not found', [], 404);
        }

        $file = $attachments[$attachmentIndex];
        $filePath = storage_path('app/public/' . $file['path']);
        if (!file_exists($filePath)) {
            return $this->sendError('File not found', [], 404);
        }

        return response()->download($filePath, $file['name']);
    }
}