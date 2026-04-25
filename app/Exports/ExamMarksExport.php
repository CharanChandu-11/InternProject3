<?php
// app/Exports/ExamMarksExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamMarksExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    public function array(): array
    {
        return $this->data;
    }
    
    public function headings(): array
    {
        return [
            'Roll No',
            'Admission No',
            'Student Name',
            'Theory Marks',
            'Practical Marks',
            'Total Marks',
            'Max Marks',
            'Percentage',
            'Grade',
            'Remarks',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}