<?php
// database/seeders/GallerySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gallery;
use App\Models\User;
use App\Models\Event;

class GallerySeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('user_type', 'super_admin')->first();
        $events = Event::all();
        
        if (!$admin) {
            $admin = User::first();
        }

        $categories = [
            'sports_day',
            'annual_function',
            'classroom_activities',
            'field_trips',
            'cultural_events',
            'achievements',
            'infrastructure',
            'workshops'
        ];
        
        $galleryItems = [
            // Sports Day Images
            [
                'title' => 'Sports Day 2024 - Opening Ceremony',
                'description' => 'Students marching during the opening ceremony of Sports Day 2024',
                'category' => 'sports_day',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/sports/sports-day-opening.jpg'
            ],
            [
                'title' => '100m Sprint Finals',
                'description' => 'Exciting finish in the 100m sprint finals for senior boys',
                'category' => 'sports_day',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/sports/100m-sprint.jpg'
            ],
            [
                'title' => 'Medal Ceremony',
                'description' => 'Winners receiving their medals from the chief guest',
                'category' => 'sports_day',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
                'image' => 'gallery/sports/medal-ceremony.jpg'
            ],
            
            // Annual Function Images
            [
                'title' => 'Annual Function 2024 - Classical Dance',
                'description' => 'Students performing classical dance at the annual function',
                'category' => 'annual_function',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/annual/classical-dance.jpg'
            ],
            [
                'title' => 'School Choir Performance',
                'description' => 'The school choir singing patriotic songs',
                'category' => 'annual_function',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/annual/choir.jpg'
            ],
            [
                'title' => 'Drama - "Swachh Bharat"',
                'description' => 'Students presenting a drama on cleanliness awareness',
                'category' => 'annual_function',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
                'image' => 'gallery/annual/drama.jpg'
            ],
            
            // Classroom Activities
            [
                'title' => 'Science Exhibition 2024',
                'description' => 'Students presenting their science projects',
                'category' => 'classroom_activities',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/classroom/science-exhibition.jpg'
            ],
            [
                'title' => 'Art Competition',
                'description' => 'Students participating in the inter-class art competition',
                'category' => 'classroom_activities',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/classroom/art-competition.jpg'
            ],
            [
                'title' => 'Computer Lab Session',
                'description' => 'Students learning programming in the computer lab',
                'category' => 'classroom_activities',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
                'image' => 'gallery/classroom/computer-lab.jpg'
            ],
            
            // Field Trips
            [
                'title' => 'Trip to Science Museum',
                'description' => 'Students exploring the interactive science exhibits',
                'category' => 'field_trips',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/trips/science-museum.jpg'
            ],
            [
                'title' => 'Historical Site Visit',
                'description' => 'Students learning about history at the fort',
                'category' => 'field_trips',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/trips/historical-site.jpg'
            ],
            
            // Cultural Events
            [
                'title' => 'Independence Day Celebration',
                'description' => 'Flag hoisting ceremony on Independence Day',
                'category' => 'cultural_events',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/cultural/independence-day.jpg'
            ],
            [
                'title' => 'Diwali Celebration',
                'description' => 'Rangoli competition during Diwali celebration',
                'category' => 'cultural_events',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/cultural/diwali.jpg'
            ],
            
            // Achievements
            [
                'title' => 'Inter-School Debate Winners',
                'description' => 'Our students winning the inter-school debate competition',
                'category' => 'achievements',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/achievements/debate-winners.jpg'
            ],
            [
                'title' => 'District Level Sports Champions',
                'description' => 'Students who won medals at district level sports meet',
                'category' => 'achievements',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/achievements/sports-champions.jpg'
            ],
            
            // Infrastructure
            [
                'title' => 'New Library',
                'description' => 'The newly renovated school library with over 5000 books',
                'category' => 'infrastructure',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/infrastructure/library.jpg'
            ],
            [
                'title' => 'Science Laboratories',
                'description' => 'Well-equipped physics and chemistry laboratories',
                'category' => 'infrastructure',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/infrastructure/labs.jpg'
            ],
            [
                'title' => 'Sports Complex',
                'description' => 'Indoor sports complex with multiple facilities',
                'category' => 'infrastructure',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
                'image' => 'gallery/infrastructure/sports-complex.jpg'
            ],
            
            // Workshops
            [
                'title' => 'Robotics Workshop',
                'description' => 'Students learning robotics and programming',
                'category' => 'workshops',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'image' => 'gallery/workshops/robotics.jpg'
            ],
            [
                'title' => 'Personality Development Session',
                'description' => 'Expert conducting personality development workshop',
                'category' => 'workshops',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 2,
                'image' => 'gallery/workshops/personality.jpg'
            ],
        ];

        foreach ($galleryItems as $index => $item) {
            // Randomly assign to an event if exists
            $eventId = null;
            if ($events->isNotEmpty() && rand(0, 1)) {
                $eventId = $events->random()->id;
            }
            
            Gallery::create([
                'title' => $item['title'],
                'description' => $item['description'],
                'image' => $item['image'],
                'category' => $item['category'],
                'event_id' => $eventId,
                'is_featured' => $item['is_featured'],
                'is_active' => $item['is_active'],
                'sort_order' => $item['sort_order'],
                'uploaded_by' => $admin->id,
                'metadata' => [
                    'size' => rand(500, 2000) * 1024, // Random size between 500KB and 2MB
                    'mime_type' => 'image/jpeg',
                    'dimensions' => '1920x1080',
                    'placeholder' => true
                ]
            ]);
        }

        $this->command->info('Gallery seeded successfully!');
    }
}