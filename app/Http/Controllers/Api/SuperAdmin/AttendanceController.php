<?php
// app/Http/Controllers/Api/SuperAdmin/AttendanceController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Http\Request;

class AttendanceController extends BaseController
{
    public function index(Request $request)
    {
        $query = Attendance::with(['attendable.user', 'markedByUser'])->where('attendable_type', Student::class);
        if ($request->has('class_id')) {
            $query->whereHas('attendable', fn($q) => $q->where('class_id', $request->class_id));
        }
        $attendances = $query->latest()->paginate($request->per_page ?? 20);
        return $this->sendPaginatedResponse($attendances, 'Attendance records retrieved');
    }

    public function store(Request $request)
    {
        // Expecting array of attendance records
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
            'attendance.*.remarks' => 'nullable|string',
        ]);

        foreach ($validated['attendance'] as $data) {
            Attendance::updateOrCreate(
                [
                    'attendable_type' => Student::class,
                    'attendable_id' => $data['student_id'],
                    'attendance_date' => $validated['date'],
                ],
                [
                    'status' => $data['status'],
                    'remarks' => $data['remarks'],
                    'marked_by' => auth()->id(),
                ]
            );
        }
        return $this->sendResponse([], 'Attendance marked');
    }

    public function show(Attendance $attendance)
    {
        return $this->sendResponse($attendance, 'Attendance record retrieved');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:present,absent,late,half_day',
            'remarks' => 'nullable|string',
        ]);
        $attendance->update($validated);
        return $this->sendResponse($attendance, 'Attendance updated');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return $this->sendResponse([], 'Attendance deleted');
    }

    public function summary(Request $request)
    {
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;
        $classId = $request->class_id;

        $studentsQuery = Student::query();
        if ($classId) $studentsQuery->where('class_id', $classId);
        $students = $studentsQuery->get();

        $summary = [];
        foreach ($students as $student) {
            $attendances = $student->attendances()
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->get();

            $summary[] = [
                'student_id' => $student->id,
                'name' => $student->user->name,
                'total_days' => $attendances->count(),
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'percentage' => $attendances->count() ? round($attendances->where('status', 'present')->count() / $attendances->count() * 100, 2) : 0,
            ];
        }

        return $this->sendResponse($summary, 'Attendance summary');
    }
}