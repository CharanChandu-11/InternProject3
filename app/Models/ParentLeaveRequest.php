<?php
// app/Models/ParentLeaveRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentLeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'parent_leave_requests';

    protected $fillable = [
        'parent_id',
        'student_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'remarks',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'attachment',
        'teacher_remarks',
        'teacher_approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'teacher_approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED_BY_TEACHER = 'approved_by_teacher';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED_BY_TEACHER => 'Approved by Teacher',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // Relationships
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApprovedByTeacher($query)
    {
        return $query->where('status', self::STATUS_APPROVED_BY_TEACHER);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->whereHas('student.class', function($q) use ($teacherId) {
            $q->whereHas('classTeacher', function($cq) use ($teacherId) {
                $cq->where('teacher_id', $teacherId);
            });
        });
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED_BY_TEACHER => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }
}