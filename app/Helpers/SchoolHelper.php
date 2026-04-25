<?php

use App\Models\SchoolSetting;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;

if (!function_exists('school')) {
    function school()
    {
        return SchoolSetting::first();
    }
}

if (!function_exists('currentAcademicYear')) {
    function currentAcademicYear()
    {
        return AcademicYear::where('is_current', true)->first();
    }
}

if (!function_exists('getUserType')) {
    function getUserType()
    {
        return Auth::check() ? Auth::user()->user_type : null;
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        return '₹ ' . number_format($amount, 2);
    }
}

if (!function_exists('getAttendanceStatusColor')) {
    function getAttendanceStatusColor($status)
    {
        return [
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'half_day' => 'info',
            'holiday' => 'secondary'
        ][$status] ?? 'secondary';
    }
}

if (!function_exists('getGradeFromMarks')) {
    function getGradeFromMarks($marks, $totalMarks)
    {
        $percentage = ($marks / $totalMarks) * 100;
        
        return match(true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F'
        };
    }
}

if (!function_exists('generateRollNumbers')) {
    function generateRollNumbers($classId, $sectionId)
    {
        $students = \App\Models\Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->orderBy('user_id')
            ->get();
        
        $rollNumber = 1;
        foreach ($students as $student) {
            $student->update(['roll_number' => $rollNumber++]);
        }
        
        return true;
    }
}

if (!function_exists('calculateAge')) {
    function calculateAge($dateOfBirth)
    {
        return Carbon\Carbon::parse($dateOfBirth)->age;
    }
}

if (!function_exists('getDueFeesCount')) {
    function getDueFeesCount()
    {
        return \App\Models\StudentFee::whereIn('status', ['pending', 'partial', 'overdue'])->count();
    }
}

if (!function_exists('getTodayAttendancePercentage')) {
    function getTodayAttendancePercentage()
    {
        $total = \App\Models\Attendance::whereDate('attendance_date', today())->count();
        if ($total == 0) return 0;
        
        $present = \App\Models\Attendance::whereDate('attendance_date', today())
            ->where('status', 'present')
            ->count();
            
        return round(($present / $total) * 100, 2);
    }
}