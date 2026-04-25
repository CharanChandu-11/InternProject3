<?php
// app/Models/SchoolSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $table = 'school_settings';

    protected $fillable = [
        'school_name',
        'school_code',
        'affiliation_number',
        'board',
        'address',
        'city',
        'state',
        'pincode',
        'phone',
        'email',
        'website',
        'logo',
        'social_links',
        'established_year',
        'principal_name',
        'principal_message',
        'about_school',
        'mission_statement',
        'vision_statement'
    ];

    protected $casts = [
        'social_links' => 'array'
    ];

    // Singleton pattern - only one record
    public static function getSettings()
    {
        return self::first() ?? new self();
    }
}