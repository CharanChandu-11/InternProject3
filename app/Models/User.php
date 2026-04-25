<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'phone',
        'address',
        'profile_photo',
        'user_type',
        'is_active',
        'last_login_at',
        'last_login_ip'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Relationship with Profile
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    // Relationship with Student
    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    // Relationship with Employee
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    // Relationship with ParentModel
    public function parent()
    {
        return $this->hasOne(ParentModel::class, 'user_id');
    }

    // Children relationship (if user is a parent)
    public function children()
    {
        return $this->belongsToMany(Student::class, 'student_parents', 'parent_id', 'student_id')
                    ->withPivot('relationship', 'is_primary_contact')
                    ->withTimestamps();
    }

    // Students relationship (alias for children)
    public function students()
    {
        return $this->children();
    }

    // Teaching subjects (for teachers)
    public function teachingSubjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subjects', 'teacher_id', 'subject_id')
                    ->withPivot('class_id', 'theory_marks', 'practical_marks')
                    ->withTimestamps();
    }

    // Teaching classes (for teachers)
    public function teachingClasses()
    {
        return $this->belongsToMany(Classes::class, 'class_subjects', 'teacher_id', 'class_id')
                    ->distinct();
    }

    // Timetable entries
    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'teacher_id');
    }

    // Homework assigned
    public function homeworks()
    {
        return $this->hasMany(Homework::class, 'teacher_id');
    }

    // Attendance (polymorphic)
    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    // Leave applications
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class, 'user_id');
    }

    // Messages sent
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Messages received
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // Activity logs
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    // Leave applications
    public function leaves()
    {
        return $this->hasMany(LeaveApplication::class, 'user_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function classTimetables()
    {
        return $this->hasMany(Timetable::class, 'teacher_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    // Accessor for profile photo URL
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo 
            ? asset('storage/' . $this->profile_photo)
            : asset('images/default-avatar.png');
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return $this->name;
    }
}