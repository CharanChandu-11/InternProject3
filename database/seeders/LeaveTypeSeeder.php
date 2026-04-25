<?php
// database/seeders/LeaveTypeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run()
    {
        $leaveTypes = [
            ['name' => 'Casual Leave', 'days_allowed' => 12, 'applicable_for' => 'both'],
            ['name' => 'Sick Leave', 'days_allowed' => 10, 'applicable_for' => 'both'],
            ['name' => 'Earned Leave', 'days_allowed' => 15, 'applicable_for' => 'both'],
            ['name' => 'Maternity Leave', 'days_allowed' => 90, 'applicable_for' => 'employee'],
            ['name' => 'Paternity Leave', 'days_allowed' => 15, 'applicable_for' => 'employee'],
            ['name' => 'Bereavement Leave', 'days_allowed' => 5, 'applicable_for' => 'both'],
            ['name' => 'Study Leave', 'days_allowed' => 10, 'applicable_for' => 'teacher'],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::create($type);
        }

        $this->command->info('Leave types created successfully!');
    }
}