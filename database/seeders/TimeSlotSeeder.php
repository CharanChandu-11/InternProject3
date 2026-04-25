<?php
// database/seeders/TimeSlotSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;

class TimeSlotSeeder extends Seeder
{
    public function run()
    {
        $timeSlots = [
            ['name' => 'Period 1', 'start_time' => '08:00:00', 'end_time' => '08:45:00', 'is_break' => false],
            ['name' => 'Period 2', 'start_time' => '08:45:00', 'end_time' => '09:30:00', 'is_break' => false],
            ['name' => 'Break', 'start_time' => '09:30:00', 'end_time' => '09:45:00', 'is_break' => true],
            ['name' => 'Period 3', 'start_time' => '09:45:00', 'end_time' => '10:30:00', 'is_break' => false],
            ['name' => 'Period 4', 'start_time' => '10:30:00', 'end_time' => '11:15:00', 'is_break' => false],
            ['name' => 'Period 5', 'start_time' => '11:15:00', 'end_time' => '12:00:00', 'is_break' => false],
            ['name' => 'Lunch', 'start_time' => '12:00:00', 'end_time' => '12:45:00', 'is_break' => true],
            ['name' => 'Period 6', 'start_time' => '12:45:00', 'end_time' => '13:30:00', 'is_break' => false],
            ['name' => 'Period 7', 'start_time' => '13:30:00', 'end_time' => '14:15:00', 'is_break' => false],
            ['name' => 'Period 8', 'start_time' => '14:15:00', 'end_time' => '15:00:00', 'is_break' => false],
        ];

        foreach ($timeSlots as $slot) {
            TimeSlot::create($slot);
        }

        $this->command->info('Time slots created successfully!');
    }
}