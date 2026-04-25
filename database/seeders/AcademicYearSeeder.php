<?php
// database/seeders/AcademicYearSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    public function run()
    {
        AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-04-01',
            'end_date' => '2025-03-31',
            'is_current' => true
        ]);

        AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-04-01',
            'end_date' => '2024-03-31',
            'is_current' => false
        ]);

        $this->command->info('Academic years created successfully!');
    }
}