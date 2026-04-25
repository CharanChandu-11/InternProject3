<?php
// app/Http/Controllers/Api/SuperAdmin/ReportController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Student;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function studentPerformance(Request $request)
    {
        $student = Student::with('examResults')->find($request->student_id);
        $results = $student->examResults()->with('examSchedule')->get();
        return $this->sendResponse($results, 'Student performance');
    }

    public function teacherPerformance(Request $request)
    {
        $teacher = User::with('teachingSubjects')->find($request->teacher_id);
        // Example summary
        return $this->sendResponse($teacher, 'Teacher performance');
    }

    public function attendance(Request $request)
    {
        $from = $request->from_date;
        $to = $request->to_date;
        $attendances = Attendance::whereBetween('attendance_date', [$from, $to])->get();
        return $this->sendResponse($attendances, 'Attendance report');
    }

    public function fees(Request $request)
    {
        $payments = Payment::whereBetween('payment_date', [$request->from_date, $request->to_date])->get();
        return $this->sendResponse($payments, 'Fees report');
    }

    public function examResults(Request $request)
    {
        $examId = $request->exam_id;
        $results = \App\Models\ExamResult::whereHas('examSchedule', fn($q) => $q->where('exam_id', $examId))->get();
        return $this->sendResponse($results, 'Exam results report');
    }

    public function financial(Request $request)
    {
        $revenue = Payment::sum('amount');
        $expenses = 0; // placeholder
        return $this->sendResponse(['revenue' => $revenue, 'expenses' => $expenses], 'Financial report');
    }

    public function attendanceOverview(Request $request)
    {
        $overview = [
            'total_students' => Student::count(),
            'average_attendance' => Attendance::where('attendable_type', Student::class)->avg('status') // custom logic
        ];
        return $this->sendResponse($overview, 'Attendance overview');
    }
}