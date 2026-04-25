<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Student;
use App\Models\Employee;
use App\Models\ParentModel;
use App\Models\Classes;
use App\Models\Section;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Make sure roles exist before assigning them
        $this->ensureRolesExist();
        
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@school.com',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'address' => 'School Head Office',
            'user_type' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $superAdmin->assignRole('super-admin');
        
        UserProfile::create([
            'user_id' => $superAdmin->id,
            'date_of_birth' => '1980-01-01',
            'gender' => 'male',
            'qualification' => 'MBA, PhD in Education',
            'experience_years' => 15,
            'bio' => 'Experienced education administrator with 15+ years in school management.'
        ]);

        // Create Admin
        $admin = User::create([
            'name' => 'School Admin',
            'email' => 'admin@school.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'phone' => '+1234567891',
            'address' => 'School Administrative Office',
            'user_type' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $admin->assignRole('admin');
        
        UserProfile::create([
            'user_id' => $admin->id,
            'date_of_birth' => '1985-05-15',
            'gender' => 'female',
            'qualification' => 'M.Ed, B.Ed',
            'experience_years' => 10,
            'bio' => 'Dedicated school administrator with focus on academic excellence.'
        ]);

        // Create Teacher
        $teacher = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@school.com',
            'username' => 'teacher',
            'password' => Hash::make('password'),
            'phone' => '+1234567892',
            'address' => 'Teacher Colony',
            'user_type' => 'teacher',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $teacher->assignRole('teacher');
        
        UserProfile::create([
            'user_id' => $teacher->id,
            'date_of_birth' => '1988-08-20',
            'gender' => 'male',
            'qualification' => 'M.Sc Mathematics, B.Ed',
            'experience_years' => 8,
            'bio' => 'Passionate mathematics teacher with innovative teaching methods.'
        ]);

        // Create Teacher record
        Employee::create([
            'user_id' => $teacher->id,
            'employee_id' => 'TCH001',
            'employment_type' => 'full_time',
            'joining_date' => '2020-06-01',
            'department' => 'Academic',
            'designation' => 'Mathematics Teacher',
            'qualification' => 'M.Sc Mathematics, B.Ed',
            'experience_years' => 8,
        ]);

        // Create Employee (non-teaching staff)
        $employee = User::create([
            'name' => 'Jane Employee',
            'email' => 'employee@school.com',
            'username' => 'employee',
            'password' => Hash::make('password'),
            'phone' => '+1234567893',
            'address' => 'Staff Quarters',
            'user_type' => 'employee',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $employee->assignRole('employee');
        
        UserProfile::create([
            'user_id' => $employee->id,
            'date_of_birth' => '1990-03-10',
            'gender' => 'female',
            'qualification' => 'B.Com, MBA',
            'experience_years' => 5,
            'bio' => 'Efficient administrative staff member.'
        ]);

        // Create Employee record
        Employee::create([
            'user_id' => $employee->id,
            'employee_id' => 'EMP001',
            'employment_type' => 'full_time',
            'joining_date' => '2020-01-15',
            'department' => 'Administration',
            'designation' => 'Office Assistant',
            'qualification' => 'B.Com',
            'experience_years' => 5,
        ]);

        // Create Parent 1 - Father
        $father = User::create([
            'name' => 'Robert Parent',
            'email' => 'father@school.com',
            'username' => 'father',
            'password' => Hash::make('password'),
            'phone' => '+1234567894',
            'address' => 'Parent Avenue, House No. 123',
            'user_type' => 'parent',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $father->assignRole('parent');
        
        UserProfile::create([
            'user_id' => $father->id,
            'date_of_birth' => '1975-12-25',
            'gender' => 'male',
            'qualification' => 'B.Tech, MBA',
            'experience_years' => 20,
            'bio' => 'Engineer and active parent.'
        ]);

        // Create Parent record - Father
        $fatherRecord = ParentModel::create([
            'user_id' => $father->id,
            'parent_type' => 'father',
            'occupation' => 'Software Engineer',
            'office_address' => 'Tech Park, Floor 5',
            'office_phone' => '+1234567899',
        ]);

        // Create Parent 2 - Mother
        $mother = User::create([
            'name' => 'Sarah Parent',
            'email' => 'mother@school.com',
            'username' => 'mother',
            'password' => Hash::make('password'),
            'phone' => '+1234567896',
            'address' => 'Parent Avenue, House No. 123',
            'user_type' => 'parent',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $mother->assignRole('parent');
        
        UserProfile::create([
            'user_id' => $mother->id,
            'date_of_birth' => '1978-08-15',
            'gender' => 'female',
            'qualification' => 'M.Sc, B.Ed',
            'experience_years' => 15,
            'bio' => 'Teacher and caring mother.'
        ]);

        // Create Parent record - Mother
        $motherRecord = ParentModel::create([
            'user_id' => $mother->id,
            'parent_type' => 'mother',
            'occupation' => 'School Teacher',
            'office_address' => 'City School',
            'office_phone' => '+1234567897',
        ]);

        // Create Student
        $student = User::create([
            'name' => 'Tom Student',
            'email' => 'student@school.com',
            'username' => 'student',
            'password' => Hash::make('password'),
            'phone' => '+1234567895',
            'address' => 'Student Hostel, Room 101',
            'user_type' => 'student',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $student->assignRole('student');
        
        UserProfile::create([
            'user_id' => $student->id,
            'date_of_birth' => '2010-06-30',
            'gender' => 'male',
            'blood_group' => 'O+',
        ]);

        // Get the current academic year
        $academicYear = AcademicYear::where('is_current', true)->first();
        
        // If no academic year exists, create one
        if (!$academicYear) {
            $academicYear = AcademicYear::create([
                'name' => '2024-2025',
                'start_date' => '2024-04-01',
                'end_date' => '2025-03-31',
                'is_current' => true
            ]);
        }
        
        // Get first class and section, or create them
        $firstClass = Classes::first();
        if (!$firstClass) {
            $firstClass = Classes::create([
                'name' => 'Class 1',
                'numeric_name' => 1,
                'academic_year_id' => $academicYear->id,
                'capacity' => 40
            ]);
        }
        
        $firstSection = $firstClass->sections()->first();
        if (!$firstSection) {
            $firstSection = Section::create([
                'name' => 'A',
                'class_id' => $firstClass->id,
                'capacity' => 40
            ]);
        }
        
        // Create Student record
        $studentRecord = Student::create([
            'user_id' => $student->id,
            'admission_number' => 'ADM2024001',
            'admission_date' => '2024-04-01',
            'class_id' => $firstClass->id,
            'section_id' => $firstSection->id,
            'academic_year_id' => $academicYear->id,
            'roll_number' => 1,
        ]);

        // Attach parents to student
        if (isset($fatherRecord) && isset($motherRecord)) {
            $studentRecord->parents()->attach($father->id, [
                'relationship' => 'father',
                'is_primary_contact' => true
            ]);
            
            $studentRecord->parents()->attach($mother->id, [
                'relationship' => 'mother',
                'is_primary_contact' => false
            ]);
        }

        $this->command->info('Users created successfully!');
        $this->command->info('=====================================');
        $this->command->info('🔑 DEFAULT LOGIN CREDENTIALS:');
        $this->command->info('=====================================');
        $this->command->info('Super Admin: superadmin@school.com / password');
        $this->command->info('Admin: admin@school.com / password');
        $this->command->info('Teacher: teacher@school.com / password');
        $this->command->info('Employee: employee@school.com / password');
        $this->command->info('Father: father@school.com / password');
        $this->command->info('Mother: mother@school.com / password');
        $this->command->info('Student: student@school.com / password');
        $this->command->info('=====================================');
    }

    private function ensureRolesExist()
    {
        $roles = ['super-admin', 'admin', 'teacher', 'student', 'parent', 'employee'];
        
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->where('guard_name', 'web')->exists()) {
                Role::create(['name' => $roleName, 'guard_name' => 'web']);
                $this->command->info("Created role: {$roleName}");
            }
        }
    }
}