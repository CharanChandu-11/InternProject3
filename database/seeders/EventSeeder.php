<?php
// database/seeders/EventSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('user_type', 'super_admin')->first();
        
        if (!$admin) {
            $admin = User::first();
        }

        $events = [
            [
                'title' => 'Annual Sports Day 2026',
                'description' => 'The Annual Sports Day featuring athletics, team sports, and fun games for all students. Parents are cordially invited to cheer for their children.',
                'type' => 'sports',
                'start_date' => Carbon::create(2026, 12, 10),
                'end_date' => Carbon::create(2026, 12, 12),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'venue' => 'School Sports Ground',
                'audience' => 'all',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Annual Function 2026 - "Celebration of Excellence"',
                'description' => 'The annual cultural program featuring dance, music, drama performances by students. Prize distribution for academic and extracurricular achievements.',
                'type' => 'cultural',
                'start_date' => Carbon::create(2026, 11, 20),
                'end_date' => Carbon::create(2026, 11, 20),
                'start_time' => '17:00:00',
                'end_time' => '21:00:00',
                'venue' => 'School Auditorium',
                'audience' => 'all',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Parent-Teacher Meeting - Term 1',
                'description' => 'Quarterly parent-teacher meeting to discuss student progress, academic performance, and overall development.',
                'type' => 'meeting',
                'start_date' => Carbon::create(2026, 5, 15),
                'end_date' => Carbon::create(2026, 5, 15),
                'start_time' => '09:00:00',
                'end_time' => '14:00:00',
                'venue' => 'Respective Classrooms',
                'audience' => 'students',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Science Exhibition 2026',
                'description' => 'Students showcase their innovative science projects and models. Competition for best project with attractive prizes.',
                'type' => 'academic',
                'start_date' => Carbon::create(2026, 8, 20),
                'end_date' => Carbon::create(2026, 8, 21),
                'start_time' => '10:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Science Block',
                'audience' => 'students',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Independence Day Celebration',
                'description' => 'Celebration of 78th Independence Day with flag hoisting, patriotic songs, and cultural performances.',
                'type' => 'cultural',
                'start_date' => Carbon::create(2026, 8, 15),
                'end_date' => Carbon::create(2026, 8, 15),
                'start_time' => '08:00:00',
                'end_time' => '11:00:00',
                'venue' => 'School Ground',
                'audience' => 'all',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Educational Trip - Science Museum',
                'description' => 'Educational trip to the Regional Science Museum for students of classes 6-8. Limited seats available.',
                'type' => 'sports',
                'start_date' => Carbon::create(2026, 9, 10),
                'end_date' => Carbon::create(2026, 9, 10),
                'start_time' => '08:30:00',
                'end_time' => '16:30:00',
                'venue' => 'City Science Museum',
                'audience' => 'students',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Teachers Day Celebration',
                'description' => 'Students celebrate Teachers Day with special performances, games, and expressions of gratitude for their teachers.',
                'type' => 'cultural',
                'start_date' => Carbon::create(2026, 9, 5),
                'end_date' => Carbon::create(2026, 9, 5),
                'start_time' => '10:00:00',
                'end_time' => '13:00:00',
                'venue' => 'School Auditorium',
                'audience' => 'teachers',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Career Counseling Session',
                'description' => 'Career counseling session for Class 10 and 12 students on various career options after school. Expert counselors will guide students.',
                'type' => 'academic',
                'start_date' => Carbon::create(2026, 10, 15),
                'end_date' => Carbon::create(2026, 10, 15),
                'start_time' => '11:00:00',
                'end_time' => '14:00:00',
                'venue' => 'Conference Hall',
                'audience' => 'students',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Diwali Celebration',
                'description' => 'Celebration of the festival of lights with rangoli competition, diya decoration, and cultural programs.',
                'type' => 'cultural',
                'start_date' => Carbon::create(2026, 10, 30),
                'end_date' => Carbon::create(2026, 10, 30),
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
                'venue' => 'School Premises',
                'audience' => 'all',
                'created_by' => $admin->id
            ],
            [
                'title' => 'Winter Break',
                'description' => 'School remains closed for winter break. Classes will resume on January 2nd, 2025.',
                'type' => 'holiday',
                'start_date' => Carbon::create(2026, 12, 23),
                'end_date' => Carbon::create(2025, 1, 1),
                'start_time' => null,
                'end_time' => null,
                'venue' => null,
                'audience' => 'all',
                'created_by' => $admin->id
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        $this->command->info('Events seeded successfully!');
    }
}