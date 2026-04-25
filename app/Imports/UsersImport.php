<?php
// app/Imports/UsersImport.php

namespace App\Imports;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class UsersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        Validator::make($rows->toArray(), [
            '*.name' => 'required|string',
            '*.email' => 'required|email|unique:users,email',
            '*.username' => 'required|unique:users,username',
            '*.phone' => 'required|string',
            '*.user_type' => 'required|in:super_admin,admin,teacher,employee,parent,student',
        ])->validate();
        
        foreach ($rows as $row) {
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'username' => $row['username'],
                'password' => Hash::make('password123'),
                'phone' => $row['phone'],
                'address' => $row['address'] ?? null,
                'user_type' => $row['user_type'],
                'is_active' => true,
            ]);
            
            $user->assignRole($row['user_type']);
            
            if ($row['date_of_birth'] || $row['gender']) {
                UserProfile::create([
                    'user_id' => $user->id,
                    'date_of_birth' => $row['date_of_birth'] ?? null,
                    'gender' => $row['gender'] ?? null,
                ]);
            }
        }
    }
}