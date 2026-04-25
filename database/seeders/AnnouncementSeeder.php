<?php
// database/seeders/AnnouncementSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\User;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('user_type', 'super_admin')->first();
        
        if (!$admin) {
            $admin = User::first();
        }

        $announcements = [
            [
                'title' => 'School Reopening for Academic Year 2024-25',
                'content' => 'Dear Parents and Students, We are pleased to announce that the school will reopen for the new academic year 2024-25 on April 1st, 2024. All students are requested to report to their respective classes by 8:00 AM. The school uniform and books will be distributed on March 30th, 2024.',
                'audience' => 'all',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->subDays(5),
                'expiry_date' => Carbon::now()->addMonths(6),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'Parent-Teacher Meeting Schedule',
                'content' => 'The first Parent-Teacher Meeting for the academic year 2024-25 will be held on May 15th, 2024 from 9:00 AM to 2:00 PM. Parents are requested to meet the respective class teachers to discuss their ward\'s progress. Please collect the progress report from the class teacher during the meeting.',
                'audience' => 'parents',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->subDays(3),
                'expiry_date' => Carbon::now()->addDays(25),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'Annual Sports Day 2024 - Registration Open',
                'content' => 'The Annual Sports Day 2024 will be held on December 10th-12th, 2024. Students interested in participating can register with their respective class teachers by November 15th, 2024. Events include athletics, team sports, and fun games. Winners will receive certificates and trophies.',
                'audience' => 'students',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->subDays(1),
                'expiry_date' => Carbon::now()->addMonths(2),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'Summer Vacation Schedule',
                'content' => 'The school will remain closed for summer vacation from May 20th to June 15th, 2024. The school will reopen on June 16th, 2024. Summer assignment books have been distributed to all students. Please ensure that your child completes the assignments during the vacation.',
                'audience' => 'all',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->subDays(7),
                'expiry_date' => Carbon::now()->addMonths(3),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'Staff Meeting - Important',
                'content' => 'A mandatory staff meeting will be held on March 25th, 2024 at 3:00 PM in the conference hall. All teaching and non-teaching staff are requested to attend. Agenda includes discussion on new academic year planning and curriculum updates.',
                'audience' => 'employees',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->subDays(2),
                'expiry_date' => Carbon::now()->addDays(10),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'Class 10 and 12 Board Exam Results',
                'content' => 'The CBSE Board Exam results for Class 10 and 12 will be announced on May 15th, 2024. Students can check their results on the official CBSE website. The school will also provide printed mark sheets once received from the board.',
                'audience' => 'specific_classes',
                'specific_classes' => ['10', '12'],
                'publish_date' => Carbon::now()->addDays(15),
                'expiry_date' => Carbon::now()->addMonths(2),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'Science Exhibition 2024',
                'content' => 'The Annual Science Exhibition will be held on August 20th, 2024. Students from classes 6 to 12 can participate with working models or projects. The last date for registration is August 5th, 2024. Prizes will be awarded for the best projects.',
                'audience' => 'students',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->addDays(20),
                'expiry_date' => Carbon::now()->addMonths(3),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'School Holiday on Account of Local Festival',
                'content' => 'The school will remain closed on March 25th, 2024 on account of the local festival. All classes and activities scheduled for the day are cancelled. The school will function as usual on the following day.',
                'audience' => 'all',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->subDays(10),
                'expiry_date' => Carbon::now()->addDays(5),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'New Library Books Arrived',
                'content' => 'We are excited to announce that 500 new books have been added to the school library. The collection includes storybooks, encyclopedias, competitive exam books, and reference materials. Students can visit the library during library periods to issue books.',
                'audience' => 'students',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->subDays(3),
                'expiry_date' => Carbon::now()->addMonths(4),
                'is_published' => true,
                'created_by' => $admin->id
            ],
            [
                'title' => 'Teacher Training Workshop',
                'content' => 'A two-day teacher training workshop on "Modern Teaching Methodologies" will be conducted on April 5th-6th, 2024. All teachers are requested to attend without fail. The workshop will be conducted by experts from the education department.',
                'audience' => 'teachers',
                'specific_classes' => null,
                'publish_date' => Carbon::now()->addDays(5),
                'expiry_date' => Carbon::now()->addDays(20),
                'is_published' => true,
                'created_by' => $admin->id
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }

        $this->command->info('Announcements seeded successfully!');
    }
}