<?php
// database/seeders/ClassSectionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\Section;
use App\Models\AcademicYear;

class ClassSectionSeeder extends Seeder
{
    public function run()
    {
        $academicYear = AcademicYear::where('is_current', true)->first();
        
        $classes = [
            ['name' => 'Class 1', 'numeric_name' => 1, 'capacity' => 40],
            ['name' => 'Class 2', 'numeric_name' => 2, 'capacity' => 40],
            ['name' => 'Class 3', 'numeric_name' => 3, 'capacity' => 40],
            ['name' => 'Class 4', 'numeric_name' => 4, 'capacity' => 40],
            ['name' => 'Class 5', 'numeric_name' => 5, 'capacity' => 40],
            ['name' => 'Class 6', 'numeric_name' => 6, 'capacity' => 40],
            ['name' => 'Class 7', 'numeric_name' => 7, 'capacity' => 40],
            ['name' => 'Class 8', 'numeric_name' => 8, 'capacity' => 40],
            ['name' => 'Class 9', 'numeric_name' => 9, 'capacity' => 40],
            ['name' => 'Class 10', 'numeric_name' => 10, 'capacity' => 40],
            ['name' => 'Class 11', 'numeric_name' => 11, 'capacity' => 40],
            ['name' => 'Class 12', 'numeric_name' => 12, 'capacity' => 40],
        ];

        foreach ($classes as $classData) {
            $class = Classes::create([
                'name' => $classData['name'],
                'numeric_name' => $classData['numeric_name'],
                'academic_year_id' => $academicYear->id,
                'capacity' => $classData['capacity']
            ]);

            // Create sections for each class
            $sections = ['A', 'B', 'C'];
            foreach ($sections as $sectionName) {
                Section::create([
                    'name' => $sectionName,
                    'class_id' => $class->id,
                    'capacity' => 40
                ]);
            }
        }

        $this->command->info('Classes and sections created successfully!');
    }
}