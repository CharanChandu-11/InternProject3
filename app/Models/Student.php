<?php
// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'admission_number',
        'admission_date',
        'class_id',
        'section_id',
        'academic_year_id',
        'roll_number',
        'previous_school',
        'previous_grade'
    ];

    protected $casts = [
        'admission_date' => 'date',
        'previous_grade' => 'decimal:2'
    ];

    // Relationship with User (NOT "user" - this is correct)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with Class
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // Relationship with Section
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // Relationship with Academic Year
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // Relationship with Parents
    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'student_parents', 'student_id', 'parent_id')
                    ->withPivot('relationship', 'is_primary_contact')
                    ->withTimestamps();
    }

    // Attendance
    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    // Fees
    public function fees()
    {
        return $this->hasMany(StudentFee::class);
    }

    // Payments
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Exam Results
    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    // Homework Submissions
    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    // Book Issues
    public function bookIssues()
    {
        return $this->morphMany(BookIssue::class, 'issuable');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->user?->name;
    }

    public function getClassNameAttribute()
    {
        return $this->class?->name;
    }

    public function getSectionNameAttribute()
    {
        return $this->section?->name;
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->user?->profile_photo_url;
    }

    public function getEmailAttribute()
    {
        return $this->user?->email;
    }

    public function getPhoneAttribute()
    {
        return $this->user?->phone;
    }

    public function getAddressAttribute()
    {
        return $this->user?->address;
    }

    public function getAttendancePercentageAttribute()
    {
        $totalDays = $this->attendances()->count();
        if ($totalDays == 0) return 0;
        
        $presentDays = $this->attendances()->where('status', 'present')->count();
        return round(($presentDays / $totalDays) * 100, 2);
    }

    public function getTotalFeesAttribute()
    {
        return $this->fees()->sum('total_amount');
    }

    public function getPaidFeesAttribute()
    {
        return $this->fees()->sum('paid_amount');
    }

    public function getDueFeesAttribute()
    {
        return $this->fees()->whereIn('status', ['pending', 'partial'])->sum('due_amount');
    }

    public function getAverageMarksAttribute()
    {
        return round($this->examResults()->avg('percentage'), 2);
    }

    public function getMonthlyAttendancePercentageAttribute()
    {
        $attendances = $this->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->get();
            
        $totalDays = $attendances->count();
        
        if ($totalDays == 0) {
            return 0;
        }
        
        $presentDays = $attendances->where('status', 'present')->count();
        
        return round(($presentDays / $totalDays) * 100, 2);
    }

    public function getTodayAttendanceAttribute()
    {
        return $this->attendances()
            ->whereDate('attendance_date', today())
            ->first();
    }

    public function hostelAllocation()
    {
        return $this->hasOne(HostelAllocation::class)->where('status', HostelAllocation::STATUS_ACTIVE);
    }
}