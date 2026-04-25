<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolSetting;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Models\LeaveType;
use App\Models\FeeCategory;
use App\Models\ExamType;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        // DB::statement('PRAGMA foreign_keys=OFF');
        
        // Clear existing data
        // $this->clearExistingData();
        
        // Enable foreign key checks
        // DB::statement('PRAGMA foreign_keys=ON');
        
        // Seed in correct order
        $this->call([
            PermissionRoleSeeder::class,
            UserSeeder::class,
            SchoolSettingSeeder::class,
            AcademicYearSeeder::class,
            ClassSectionSeeder::class,
            SubjectSeeder::class,
            TimeSlotSeeder::class,
            LeaveTypeSeeder::class,
            FeeCategorySeeder::class,
            ExamTypeSeeder::class,
        ]);
    }
    
    private function clearExistingData()
    {
        // Delete in reverse order of dependencies
        DB::table('model_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();
        DB::table('users')->delete();
        DB::table('school_settings')->delete();
        DB::table('academic_years')->delete();
        DB::table('classes')->delete();
        DB::table('sections')->delete();
        DB::table('subjects')->delete();
        DB::table('time_slots')->delete();
        DB::table('leave_types')->delete();
        DB::table('fee_categories')->delete();
        DB::table('exam_types')->delete();
    }
}