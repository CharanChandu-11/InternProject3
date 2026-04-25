<?php
// app/Exports/UsersExport.php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }
    
    public function query()
    {
        $query = User::query();
        
        if (!empty($this->filters['user_type'])) {
            $query->where('user_type', $this->filters['user_type']);
        }
        
        if (!empty($this->filters['status'])) {
            $query->where('is_active', $this->filters['status'] === 'active');
        }
        
        return $query;
    }
    
    public function headings(): array
    {
        return [
            'ID', 'Name', 'Email', 'Username', 'Phone', 'User Type', 'Status', 
            'Date of Birth', 'Gender', 'Address', 'Created At'
        ];
    }
    
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->username,
            $user->phone,
            $user->user_type,
            $user->is_active ? 'Active' : 'Inactive',
            $user->profile?->date_of_birth?->format('Y-m-d'),
            $user->profile?->gender,
            $user->address,
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}