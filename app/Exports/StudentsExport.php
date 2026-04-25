<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $classId;
    protected $sectionId;

    public function __construct($classId = null, $sectionId = null)
    {
        $this->classId = $classId;
        $this->sectionId = $sectionId;
    }

    public function collection()
    {
        $query = Student::with(['user', 'class', 'section']);
        
        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }
        
        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }
        
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Admission No',
            'Student Name',
            'Class',
            'Section',
            'Roll No',
            'Father Name',
            'Mother Name',
            'Phone',
            'Email',
            'Address'
        ];
    }

    public function map($student): array
    {
        return [
            $student->admission_number,
            $student->user->name,
            $student->class->name,
            $student->section->name,
            $student->roll_number,
            $student->parents()->wherePivot('relationship', 'father')->first()?->name ?? 'N/A',
            $student->parents()->wherePivot('relationship', 'mother')->first()?->name ?? 'N/A',
            $student->user->phone,
            $student->user->email,
            $student->user->address
        ];
    }
}