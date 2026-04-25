<?php
// app/Models/AdmissionInquiry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionInquiry extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inquiry_number',
        'student_name',
        'student_dob',
        'student_gender',
        'class_applying_for',
        'parent_name',
        'parent_email',
        'parent_phone',
        'address',
        'status',
        'remarks',
        'follow_up_date',
        'assigned_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'student_dob' => 'date',
        'follow_up_date' => 'date',
    ];

    /**
     * Get the user (staff) assigned to this inquiry.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope a query to only include pending inquiries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include contacted inquiries.
     */
    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    /**
     * Scope a query to only include inquiries needing follow-up.
     */
    public function scopeFollowUp($query)
    {
        return $query->where('status', 'follow_up')
            ->where('follow_up_date', '<=', now());
    }

    /**
     * Scope a query to only include admitted inquiries.
     */
    public function scopeAdmitted($query)
    {
        return $query->where('status', 'admitted');
    }

    /**
     * Boot method to auto-generate inquiry number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inquiry) {
            if (empty($inquiry->inquiry_number)) {
                $inquiry->inquiry_number = static::generateInquiryNumber();
            }
        });
    }

    /**
     * Generate a unique inquiry number.
     *
     * @return string
     */
    public static function generateInquiryNumber()
    {
        $year = date('Y');
        $lastInquiry = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInquiry) {
            $lastNumber = (int) substr($lastInquiry->inquiry_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return 'INQ' . $year . $newNumber;
    }
}