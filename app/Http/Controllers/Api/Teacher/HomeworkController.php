<?php
// app/Http/Controllers/Api/Teacher/HomeworkController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\HomeworkResource;
use App\Http\Resources\HomeworkSubmissionResource;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\ClassSubject;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HomeworkController extends BaseController
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = Homework::where('teacher_id', $teacher->id)
            ->with(['class', 'section', 'subject']);
        
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('submission_date', '>=', Carbon::today())->where('status', 'active');
            } elseif ($request->status == 'expired') {
                $query->where('submission_date', '<', Carbon::today())->orWhere('status', 'expired');
            } elseif ($request->status == 'draft') {
                $query->where('status', 'draft');
            }
        }
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }   
        
        $homeworks = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse(
            HomeworkResource::collection($homeworks),
            'Homework retrieved'
        );
    }
    
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'submission_date' => 'required|date|after_or_equal:today',
            'total_marks' => 'nullable|integer|min:0',
            'attachments.*' => 'nullable|file|max:10240',
            'status' => 'nullable|in:active,draft',
        ]);

        if ($validation->fails()) {
            return $this->sendError('Validation Error', [$validation->errors()], 422);
        }   
        
        $teacher = Auth::user();
        
        $teachesSubject = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();
        
        if (!$teachesSubject) {
            return $this->sendError('Unauthorized for this subject/class', [], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('homework', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
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
                'total_marks' => $request->total_marks,
                'attachments' => json_encode($attachments),
                'status' => $request->status ?? 'active',
            ]);
            
            DB::commit();
            
            return $this->sendResponse(new HomeworkResource($homework), 'Homework created', 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create homework: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function show(Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $homework->load(['class', 'section', 'subject', 'teacher']);
        
        return $this->sendResponse(new HomeworkResource($homework), 'Homework retrieved');
    }
    
    public function update(Request $request, Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $validation = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'submission_date' => 'sometimes|date|after_or_equal:today',
            'total_marks' => 'nullable|integer|min:0',
            'attachments.*' => 'nullable|file|max:10240',
            'status' => 'nullable|in:active,draft,expired',
        ]);
        
        if ($validation->fails()) {
            return $this->sendError('Validation Error', [$validation->errors()], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $updateData = $request->only(['title', 'description', 'submission_date', 'total_marks', 'status']);
            
            if ($request->hasFile('attachments')) {
                $attachments = json_decode($homework->attachments, true) ?? [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('homework', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                    ];
                }
                $updateData['attachments'] = json_encode($attachments);
            }
            
            $homework->update($updateData);
            
            DB::commit();
            
            return $this->sendResponse(new HomeworkResource($homework), 'Homework updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update homework: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function destroy(Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $attachments = json_decode($homework->attachments, true) ?? [];
        foreach ($attachments as $attachment) {
            Storage::disk('public')->delete($attachment['path']);
        }
        
        $homework->delete();
        
        return $this->sendResponse([], 'Homework deleted');
    }
    
    public function submissions(Homework $homework)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $students = Student::where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->with('user')
            ->orderBy('roll_number')
            ->get();
        
        $submissions = $homework->submissions()
            ->get()
            ->keyBy('student_id');
        
        $data = $students->map(function($student) use ($submissions) {
            $submission = $submissions[$student->id] ?? null;
            return [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'admission_number' => $student->admission_number,
                    'roll_number' => $student->roll_number,
                    'profile_photo' => $student->user->profile_photo_url,
                ],
                'submission' => $submission ? new HomeworkSubmissionResource($submission) : null,
                'has_submitted' => !is_null($submission),
                'is_late' => $submission ? $submission->is_late : false,
                'is_graded' => $submission && $submission->status == 'graded',
            ];
        });
        
        $stats = [
            'total_students' => $students->count(),
            'submitted' => $data->where('has_submitted', true)->count(),
            'not_submitted' => $data->where('has_submitted', false)->count(),
            'late' => $data->where('is_late', true)->count(),
            'graded' => $data->where('is_graded', true)->count(),
        ];
        
        return $this->sendResponse([
            'submissions' => $data,
            'statistics' => $stats,
        ], 'Submissions retrieved');
    }
    
    public function grade(Request $request, Homework $homework, HomeworkSubmission $submission)
    {
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        if ($submission->homework_id != $homework->id) {
            return $this->sendError('Invalid submission', [], 400);
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
        
        return $this->sendResponse(new HomeworkSubmissionResource($submission), 'Submission graded');
    }
    
    public function downloadAttachment($homeworkId, $attachmentIndex)
    {
        $homework = Homework::findOrFail($homeworkId);
        $teacher = Auth::user();
        
        if ($homework->teacher_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $attachments = json_decode($homework->attachments, true) ?? [];
        
        if (!isset($attachments[$attachmentIndex])) {
            return $this->sendError('Attachment not found', [], 404);
        }
        
        $attachment = $attachments[$attachmentIndex];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        if (!file_exists($filePath)) {
            return $this->sendError('File not found', [], 404);
        }
        
        return response()->download($filePath, $attachment['name']);
    }
}