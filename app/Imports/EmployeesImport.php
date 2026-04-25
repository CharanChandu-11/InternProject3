<?php
// app/Imports/EmployeesImport.php

namespace App\Imports;

use App\Models\User;
use App\Models\Employee;
use App\Models\UserProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Create user
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'username' => $row['username'],
                'password' => Hash::make('password123'),
                'phone' => $row['phone'],
                'address' => $row['address'] ?? null,
                'user_type' => 'employee',
                'is_active' => true,
            ]);
            
            $user->assignRole('employee');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $row['date_of_birth'] ?? null,
                'gender' => $row['gender'] ?? null,
                'qualification' => $row['qualification'] ?? null,
                'experience_years' => $row['experience_years'] ?? null,
                'emergency_contact' => $row['emergency_contact'] ?? null,
                'emergency_contact_name' => $row['emergency_contact_name'] ?? null,
            ]);
            
            // Generate employee ID
            $employeeId = $this->generateEmployeeId();
            
            // Create employee record
            Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'employment_type' => $row['employment_type'] ?? 'full_time',
                'joining_date' => $row['joining_date'] ?? now(),
                'department' => $row['department'],
                'designation' => $row['designation'],
                'salary' => $row['salary'] ?? null,
                'bank_name' => $row['bank_name'] ?? null,
                'bank_account' => $row['bank_account'] ?? null,
                'ifsc_code' => $row['ifsc_code'] ?? null,
                'pan_number' => $row['pan_number'] ?? null,
                'qualification' => $row['qualification'] ?? null,
                'experience_years' => $row['experience_years'] ?? null,
            ]);
        }
    }
    
    private function generateEmployeeId()
    {
        $year = now()->format('Y');
        $lastEmployee = Employee::whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastEmployee) {
            $lastNumber = intval(substr($lastEmployee->employee_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return 'EMP' . $year . $newNumber;
    }
}