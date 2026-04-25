<?php
// app/Imports/ParentsImport.php

namespace App\Imports;

use App\Models\User;
use App\Models\ParentModel;
use App\Models\UserProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParentsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Create user
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'username' => $row['username'],
                'password' => Hash::make('password123'),
                'phone' => $row['phone'],
                'address' => $row['address'] ?? null,
                'user_type' => 'parent',
                'is_active' => true,
            ]);
            
            $user->assignRole('parent');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $row['date_of_birth'] ?? null,
                'gender' => $row['gender'] ?? null,
                'qualification' => $row['qualification'] ?? null,
                'emergency_contact' => $row['emergency_contact'] ?? null,
                'alternative_phone' => $row['alternative_phone'] ?? null,
            ]);
            
            // Create parent record
            ParentModel::create([
                'user_id' => $user->id,
                'parent_type' => $row['parent_type'] ?? 'guardian',
                'occupation' => $row['occupation'] ?? null,
                'office_address' => $row['office_address'] ?? null,
                'office_phone' => $row['office_phone'] ?? null,
                'annual_income' => $row['annual_income'] ?? null,
                'qualification' => $row['qualification'] ?? null,
            ]);
        }
    }
}