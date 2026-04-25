<?php
// app/Models/BookIssue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'issuable_type',
        'issuable_id',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'late_fee',
        'remarks'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'late_fee' => 'decimal:2'
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function issuable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'issued')
                     ->where('due_date', '<', today());
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    // Methods
    public function calculateLateFee()
    {
        if ($this->return_date && $this->return_date > $this->due_date) {
            $daysLate = $this->due_date->diffInDays($this->return_date);
            return $daysLate * 10; // $10 per day late fee
        }
        return 0;
    }
}