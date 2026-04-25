<?php
// database/seeders/FeeCategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeCategory;

class FeeCategorySeeder extends Seeder
{
    public function run()
    {
        $feeCategories = [
            ['name' => 'Tuition Fee', 'code' => 'TUI001', 'description' => 'Monthly tuition fee'],
            ['name' => 'Transport Fee', 'code' => 'TRN001', 'description' => 'Transportation charges'],
            ['name' => 'Library Fee', 'code' => 'LIB001', 'description' => 'Library maintenance fee'],
            ['name' => 'Sports Fee', 'code' => 'SPO001', 'description' => 'Sports activities fee'],
            ['name' => 'Laboratory Fee', 'code' => 'LAB001', 'description' => 'Science lab charges'],
            ['name' => 'Computer Fee', 'code' => 'COM001', 'description' => 'Computer lab charges'],
            ['name' => 'Examination Fee', 'code' => 'EXM001', 'description' => 'Exam related charges'],
            ['name' => 'Admission Fee', 'code' => 'ADM001', 'description' => 'One-time admission fee'],
            ['name' => 'Annual Fee', 'code' => 'ANN001', 'description' => 'Annual charges'],
            ['name' => 'Hostel Fee', 'code' => 'HOS001', 'description' => 'Hostel accommodation fee'],
        ];

        foreach ($feeCategories as $category) {
            FeeCategory::create($category);
        }

        $this->command->info('Fee categories created successfully!');
    }
}