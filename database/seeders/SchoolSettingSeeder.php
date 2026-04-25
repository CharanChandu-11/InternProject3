<?php
// database/seeders/SchoolSettingSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolSetting;

class SchoolSettingSeeder extends Seeder
{
    public function run()
    {
        SchoolSetting::create([
            'school_name' => 'Smart School ERP',
            'school_code' => 'SS001',
            'affiliation_number' => 'AFF123456',
            'board' => 'CBSE',
            'address' => '123 Education Street',
            'city' => 'Tech City',
            'state' => 'Tech State',
            'pincode' => '123456',
            'phone' => '+1234567890',
            'email' => 'info@smartschool.com',
            'website' => 'www.smartschool.com',
            'logo' => null,
        ]);

        $this->command->info('School settings created successfully!');
    }
}