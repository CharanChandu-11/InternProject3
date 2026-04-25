<?php
namespace App\Services;

use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\Classes;
use Carbon\Carbon;

class FeeService
{
    /**
     * Generate fees for a single student based on his class and academic year
     */
    public static function generateForStudent(Student $student, $academicYearId = null)
    {
        if (!$academicYearId) {
            $academicYear = \App\Models\AcademicYear::where('is_current', true)->first();
            if (!$academicYear) return;
            $academicYearId = $academicYear->id;
        }

        $feeStructures = FeeStructure::where('class_id', $student->class_id)->get();

        foreach ($feeStructures as $fs) {
            // Check if already exists for this student and academic year (if you have academic_year_id in student_fees, else just avoid duplicates)
            $exists = StudentFee::where('student_id', $student->id)
                ->where('fee_structure_id', $fs->id)
                ->exists();
            if ($exists) continue;

            $dueDate = self::calculateDueDate($fs->frequency);
            StudentFee::create([
                'student_id' => $student->id,
                'fee_structure_id' => $fs->id,
                'total_amount' => $fs->amount,
                'paid_amount' => 0,
                'due_amount' => $fs->amount,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Generate fees for all students in a class (bulk)
     */
    public static function generateForClass(Classes $class, $academicYearId = null)
    {
        $students = Student::where('class_id', $class->id)->get();
        foreach ($students as $student) {
            self::generateForStudent($student, $academicYearId);
        }
    }

    /**
     * Generate fees for all students in the school (new academic year)
     */
    public static function generateForAllStudents($academicYearId = null)
    {
        $students = Student::all();
        foreach ($students as $student) {
            self::generateForStudent($student, $academicYearId);
        }
    }

    /**
     * When a new fee structure is created, generate fees for existing students of that class
     */
    public static function generateForNewFeeStructure(FeeStructure $feeStructure)
    {
        if($feeStructure->is_optional) {
            
            return;
        }
        $students = Student::where('class_id', $feeStructure->class_id)->get();
        foreach ($students as $student) {
            $exists = StudentFee::where('student_id', $student->id)
                ->where('fee_structure_id', $feeStructure->id)
                ->exists();
            if (!$exists) {
                $dueDate = self::calculateDueDate($feeStructure->frequency);
                StudentFee::create([
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructure->id,
                    'total_amount' => $feeStructure->amount,
                    'paid_amount' => 0,
                    'due_amount' => $feeStructure->amount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                ]);
            }
        }
    }

    protected static function calculateDueDate($frequency)
    {
        $now = Carbon::now();
        switch ($frequency) {
            case 'monthly':
                return $now->endOfMonth();
            case 'quarterly':
                return $now->addMonths(3)->endOfMonth();
            case 'half_yearly':
                return $now->addMonths(6)->endOfMonth();
            case 'yearly':
                return $now->addYear()->endOfMonth();
            default:
                return $now->addDays(30);
        }
    }
}