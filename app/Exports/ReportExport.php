<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\ExamResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReportExport
{
    public function generateProgressReport($studentId, $term)
    {
        $student = Student::with(['user', 'class', 'section'])->find($studentId);
        
        $results = ExamResult::where('student_id', $studentId)
            ->whereHas('examSchedule.exam', function($q) use ($term) {
                $q->where('term', $term);
            })
            ->with('examSchedule.subject')
            ->get();
        
        $data = [
            'student' => $student,
            'results' => $results,
            'term' => $term,
            'school' => SchoolSetting::first(),
            'date' => now()->format('d/m/Y')
        ];
        
        $pdf = PDF::loadView('exports.progress-report', $data);
        
        return $pdf->download('progress-report-'.$student->admission_number.'.pdf');
    }
    
    public function generateFeeReceipt($paymentId)
    {
        $payment = Payment::with(['student.user', 'student.class', 'fee'])
            ->find($paymentId);
        
        $data = [
            'payment' => $payment,
            'school' => SchoolSetting::first()
        ];
        
        $pdf = PDF::loadView('exports.fee-receipt', $data);
        
        return $pdf->download('receipt-'.$payment->payment_number.'.pdf');
    }
}