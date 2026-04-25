<?php
// database/seeders/ExamTypeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;

class ExamTypeSeeder extends Seeder
{
    public function run()
    {
        $examTypes = [
            ['name' => 'Unit Test 1', 'description' => 'First unit test'],
            ['name' => 'Unit Test 2', 'description' => 'Second unit test'],
            ['name' => 'Quarterly Exam', 'description' => 'Quarterly examination'],
            ['name' => 'Half Yearly Exam', 'description' => 'Half yearly examination'],
            ['name' => 'Annual Exam', 'description' => 'Annual final examination'],
            ['name' => 'Pre-Board Exam', 'description' => 'Pre-board examination for class 10 & 12'],
            ['name' => 'Weekly Test', 'description' => 'Weekly class test'],
            ['name' => 'Practical Exam', 'description' => 'Practical examination'],
        ];

        foreach ($examTypes as $type) {
            ExamType::create($type);
        }

        $this->command->info('Exam types created successfully!');
    }
}