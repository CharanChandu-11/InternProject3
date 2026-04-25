<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Create user
        $user = User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'username' => $this->generateUsername($row['name']),
            'password' => Hash::make('password123'),
            'phone' => $row['phone'],
            'address' => $row['address'],
            'user_type' => 'student',
            'is_active' => true
        ]);
        
        // Create profile
        $user->profile()->create([
            'date_of_birth' => $row['date_of_birth'],
            'gender' => $row['gender'],
            'blood_group' => $row['blood_group'] ?? null
        ]);
        
        // Find class and section
        $class = Classes::where('name', $row['class'])->first();
        
        // Create student
        return new Student([
            'user_id' => $user->id,
            'admission_number' => $this->generateAdmissionNumber(),
            'admission_date' => $row['admission_date'] ?? now(),
            'class_id' => $class->id,
            'section_id' => $class->sections()->where('name', $row['section'])->first()->id,
            'academic_year_id' => AcademicYear::where('is_current', true)->first()->id,
            'roll_number' => $row['roll_number'] ?? null
        ]);
    }
    
    private function generateUsername($name)
    {
        // Username generation logic
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $username = $base;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    private function generateAdmissionNumber()
    {
        $year = now()->format('Y');
        $lastStudent = Student::whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastStudent) {
            $lastNumber = intval(substr($lastStudent->admission_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return 'ADM' . $year . $newNumber;
    }
}