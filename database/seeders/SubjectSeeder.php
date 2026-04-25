<?php
// database/seeders/SubjectSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH01', 'type' => 'core'],
            ['name' => 'Science', 'code' => 'SCI01', 'type' => 'core'],
            ['name' => 'English', 'code' => 'ENG01', 'type' => 'core'],
            ['name' => 'Hindi', 'code' => 'HIN01', 'type' => 'language'],
            ['name' => 'Social Studies', 'code' => 'SOC01', 'type' => 'core'],
            ['name' => 'Computer Science', 'code' => 'COM01', 'type' => 'elective'],
            ['name' => 'Physics', 'code' => 'PHY01', 'type' => 'core'],
            ['name' => 'Chemistry', 'code' => 'CHE01', 'type' => 'core'],
            ['name' => 'Biology', 'code' => 'BIO01', 'type' => 'core'],
            ['name' => 'History', 'code' => 'HIS01', 'type' => 'core'],
            ['name' => 'Geography', 'code' => 'GEO01', 'type' => 'core'],
            ['name' => 'Economics', 'code' => 'ECO01', 'type' => 'elective'],
            ['name' => 'Physical Education', 'code' => 'PHE01', 'type' => 'practical'],
            ['name' => 'Art', 'code' => 'ART01', 'type' => 'practical'],
            ['name' => 'Music', 'code' => 'MUS01', 'type' => 'practical'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }

        $this->command->info('Subjects created successfully!');
    }
}