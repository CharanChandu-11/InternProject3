<?php
// database/seeders/ContactQuerySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactQuery;
use App\Models\User;
use Carbon\Carbon;

class ContactQuerySeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('user_type', 'super_admin')->first();
        
        $queries = [
            [
                'name' => 'Rajesh Kumar',
                'email' => 'rajesh.kumar@example.com',
                'phone' => '9876543210',
                'subject' => 'Admission Inquiry for Class 1',
                'message' => 'I would like to inquire about the admission process for my child who will be joining Class 1 next academic year. Could you please provide details about the admission criteria and required documents?',
                'status' => 'resolved',
                'notes' => 'Sent admission brochure and application form via email. Follow up in 2 weeks.',
                'assigned_to' => $admin->id,
                'resolved_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(5)
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@example.com',
                'phone' => '8765432109',
                'subject' => 'Fee Structure for Class 6',
                'message' => 'Could you please share the complete fee structure for Class 6 including tuition fee, transport fee, and other charges for the academic year 2024-25?',
                'status' => 'in_progress',
                'notes' => 'Fee structure shared. Awaiting confirmation for transport opt-in.',
                'assigned_to' => $admin->id,
                'resolved_at' => null,
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'name' => 'Amit Patel',
                'email' => 'amit.patel@example.com',
                'phone' => '7654321098',
                'subject' => 'School Timings Query',
                'message' => 'What are the school timings for different classes? Also, could you provide information about the after-school activity schedules?',
                'status' => 'pending',
                'notes' => null,
                'assigned_to' => null,
                'resolved_at' => null,
                'created_at' => Carbon::now()->subHours(5)
            ],
            [
                'name' => 'Sunita Reddy',
                'email' => 'sunita.reddy@example.com',
                'phone' => '6543210987',
                'subject' => 'Transfer Certificate Request',
                'message' => 'My daughter is currently in Class 8 and we are moving to another city. Could you please guide me on the process to obtain the Transfer Certificate?',
                'status' => 'resolved',
                'notes' => 'Provided TC application form and process details. Requested to submit application 2 weeks in advance.',
                'assigned_to' => $admin->id,
                'resolved_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now()->subDays(7)
            ],
            [
                'name' => 'Vikram Singh',
                'email' => 'vikram.singh@example.com',
                'phone' => '5432109876',
                'subject' => 'Transport Route Inquiry',
                'message' => 'I want to check if the school bus service is available for our area (Greenfield Colony). If yes, what is the pickup time and monthly charges?',
                'status' => 'in_progress',
                'notes' => 'Checked routes - Greenfield Colony is on Route 5. Pickup time approx 7:15 AM. Monthly charges Rs. 1200. Shared details with parent.',
                'assigned_to' => $admin->id,
                'resolved_at' => null,
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'name' => 'Neha Gupta',
                'email' => 'neha.gupta@example.com',
                'phone' => '4321098765',
                'subject' => 'Parent-Teacher Meeting Schedule',
                'message' => 'I missed the last parent-teacher meeting due to an emergency. Can I schedule a separate meeting with my child\'s class teacher?',
                'status' => 'pending',
                'notes' => null,
                'assigned_to' => null,
                'resolved_at' => null,
                'created_at' => Carbon::now()->subHours(12)
            ],
            [
                'name' => 'Anil Joshi',
                'email' => 'anil.joshi@example.com',
                'phone' => '3210987654',
                'subject' => 'Scholarship Information',
                'message' => 'Do you offer any scholarships for meritorious students? My son scored 95% in Class 5 final exams. Please provide details about available scholarships.',
                'status' => 'resolved',
                'notes' => 'Merit scholarship of 25% fee concession available for students with 90%+. Application form shared.',
                'assigned_to' => $admin->id,
                'resolved_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(4)
            ],
            [
                'name' => 'Deepa Nair',
                'email' => 'deepa.nair@example.com',
                'phone' => '2109876543',
                'subject' => 'Uniform and Books Availability',
                'message' => 'Where can we purchase the school uniform and books? Is it available in the school store or do we need to buy from outside vendors?',
                'status' => 'resolved',
                'notes' => 'Informed that uniform and books are available at school store. Store timings: 9 AM to 3 PM on weekdays.',
                'assigned_to' => $admin->id,
                'resolved_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(6)
            ],
        ];

        foreach ($queries as $query) {
            ContactQuery::create($query);
        }

        $this->command->info('Contact queries seeded successfully!');
    }
}