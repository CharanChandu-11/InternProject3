<?php
// app/Http/Controllers/Api/Student/ExamController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\ExamSchedule;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExamController extends BaseController
{
    /**
     * Get upcoming exams with filters
     */
    public function index(Request $request)
    {
        $student = Auth::user()->student;

        $query = ExamSchedule::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['exam', 'subject']);

        // Apply status filter (default: upcoming)
        if ($request->filled('status')) {
            if ($request->status == 'upcoming') {
                $query->whereHas('exam', fn($q) => $q->where('start_date', '>=', Carbon::today()));
            } elseif ($request->status == 'ongoing') {
                $query->whereHas('exam', fn($q) => $q->where('start_date', '<=', Carbon::today())->where('end_date', '>=', Carbon::today()));
            } elseif ($request->status == 'completed') {
                $query->whereHas('exam', fn($q) => $q->where('end_date', '<', Carbon::today()));
            }
        } else {
            // Default: upcoming exams
            $query->whereHas('exam', fn($q) => $q->where('start_date', '>=', Carbon::today()));
        }

        // Filter by exam type
        if ($request->filled('exam_type_id')) {
            $query->whereHas('exam', fn($q) => $q->where('exam_type_id', $request->exam_type_id));
        }

        $exams = $query->orderBy('exam_date')->paginate($request->per_page ?? 10);

        // Statistics
        $stats = [
            'upcoming' => ExamSchedule::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->whereHas('exam', fn($q) => $q->where('start_date', '>', Carbon::today()))
                ->count(),
            'ongoing' => ExamSchedule::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->whereHas('exam', fn($q) => $q->where('start_date', '<=', Carbon::today())->where('end_date', '>=', Carbon::today()))
                ->count(),
            'completed' => ExamSchedule::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->whereHas('exam', fn($q) => $q->where('end_date', '<', Carbon::today()))
                ->count(),
        ];

        $examTypes = ExamType::all();

        $data = $exams->map(fn($exam) => [
            'id' => $exam->id,
            'exam_name' => $exam->exam->name,
            'exam_type' => $exam->exam->examType->name ?? null,
            'subject' => $exam->subject->name,
            'subject_code' => $exam->subject->code,
            'date' => $exam->exam_date->toDateString(),
            'start_time' => Carbon::parse($exam->start_time)->format('h:i A'),
            'end_time' => Carbon::parse($exam->end_time)->format('h:i A'),
            'room' => $exam->room_number,
            'total_marks' => $exam->total_marks + ($exam->practical_marks ?? 0),
            'days_left' => Carbon::today()->diffInDays($exam->exam_date, false),
        ]);

        return $this->sendResponse([
            'exams' => $data,
            'statistics' => $stats,
            'exam_types' => $examTypes,
            'pagination' => [
                'current_page' => $exams->currentPage(),
                'last_page' => $exams->lastPage(),
                'per_page' => $exams->perPage(),
                'total' => $exams->total(),
            ],
        ], 'Exams retrieved successfully');
    }
}