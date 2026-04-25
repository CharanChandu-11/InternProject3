<?php
// app/Http/Controllers/Parent/ChildController.php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\Homework;
use App\Models\Timetable;
use App\Models\StudentFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChildController extends Controller
{
    public function index()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children()->with(['user', 'class', 'section'])->get();
        
        return view('parent.children.index', compact('children'));
    }
    
    public function show(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $student->load(['user', 'class', 'section', 'parents.user']);
        
        return view('parent.children.show', compact('student'));
    }
    
    public function attendance(Student $student, Request $request)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $query = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id);
        
        if ($request->filled('month')) {
            $query->whereMonth('attendance_date', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('attendance_date', $request->year);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
            ->paginate(30)
            ->appends($request->query());
        
        $summary = [
            'today' => Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $student->id)
                ->whereDate('attendance_date', today())
                ->first(),
            'overall_percentage' => $student->attendance_percentage,
        ];
        
        return view('parent.children.attendance', compact('student', 'attendances', 'summary'));
    }
    
    public function monthlyAttendance(Student $student, Request $request)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $month = $request->month ?? Carbon::now()->month;
        $year = $request->year ?? Carbon::now()->year;
        
        $attendances = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->keyBy(function($item) {
                return $item->attendance_date->format('Y-m-d');
            });
        
        $calendar = $this->generateCalendar($year, $month, $attendances);
        $stats = $this->getMonthlyStats($attendances, $year, $month);
        
        return view('parent.children.attendance-monthly', compact('student', 'calendar', 'stats', 'month', 'year'));
    }
    
    public function results(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $results = ExamResult::where('student_id', $student->id)
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('examSchedule.exam_id');
        
        $overallStats = [
            'total_exams' => $results->count(),
            'average_percentage' => $results->flatten()->avg('percentage'),
        ];
        
        return view('parent.children.results', compact('student', 'results', 'overallStats'));
    }
    
    public function resultDetail(Student $student, $examId)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $results = ExamResult::where('student_id', $student->id)
            ->whereHas('examSchedule', function($q) use ($examId) {
                $q->where('exam_id', $examId);
            })
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();
        
        $exam = $results->first()->examSchedule->exam ?? null;
        
        return view('parent.children.result-detail', compact('student', 'results', 'exam'));
    }
    
    public function fees(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $fees = StudentFee::where('student_id', $student->id)
            ->with(['feeStructure.feeCategory'])
            ->orderBy('due_date')
            ->get();
        
        $summary = [
            'total_fees' => $fees->sum('total_amount'),
            'total_paid' => $fees->sum('paid_amount'),
            'total_due' => $fees->sum('due_amount'),
        ];
        
        return view('parent.children.fees', compact('student', 'fees', 'summary'));
    }
    
    public function homework(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $homeworks = Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('status', 'active')
            ->with(['subject', 'teacher'])
            ->orderBy('submission_date')
            ->get();
        
        $submissions = $student->homeworkSubmissions()
            ->get()
            ->keyBy('homework_id');
        
        return view('parent.children.homework', compact('student', 'homeworks', 'submissions'));
    }
    
    public function homeworkDetail(Student $student, Homework $homework)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        if ($homework->class_id != $student->class_id || $homework->section_id != $student->section_id) {
            abort(404);
        }
        
        $submission = $student->homeworkSubmissions()
            ->where('homework_id', $homework->id)
            ->first();
        
        return view('parent.children.homework-detail', compact('student', 'homework', 'submission'));
    }
    
    public function timetable(Student $student)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['subject', 'teacher', 'timeSlot'])
            ->get()
            ->groupBy('day_of_week');
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = \App\Models\TimeSlot::orderBy('start_time')->get();
        
        return view('parent.children.timetable', compact('student', 'timetable', 'days', 'timeSlots'));
    }
    
    private function generateCalendar($year, $month, $attendances)
    {
        $firstDay = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startDayOfWeek = $firstDay->dayOfWeek;
        
        $calendar = [];
        $week = [];
        
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $week[] = null;
        }
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $attendance = $attendances[$date->format('Y-m-d')] ?? null;
            
            $week[] = [
                'day' => $day,
                'date' => $date,
                'status' => $attendance?->status,
                'status_text' => $attendance ? ucfirst($attendance->status) : 'Not Marked',
            ];
            
            if ($date->dayOfWeek == 6 || $day == $daysInMonth) {
                $calendar[] = $week;
                $week = [];
            }
        }
        
        return $calendar;
    }
    
    private function getMonthlyStats($attendances, $year, $month)
    {
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        
        $workingDays = $this->getWorkingDays($year, $month);
        
        return [
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'attendance_rate' => $workingDays > 0 ? round(($present / $workingDays) * 100, 2) : 0,
        ];
    }
    
    private function getWorkingDays($year, $month)
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $workingDays = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            if (!$date->isSunday()) {
                $workingDays++;
            }
        }
        return $workingDays;
    }
}