<?php
// app/Models/EmployeeSalary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalary extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'salary_template_id',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the employee associated with this salary record.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the salary template associated with this record.
     */
    public function salaryTemplate()
    {
        return $this->belongsTo(SalaryTemplate::class);
    }

    /**
     * Scope a query to only include active salary records.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include salary records effective at a given date.
     */
    public function scopeEffectiveAt($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            });
    }

    /**
     * Activate this salary record and deactivate others for the same employee.
     */
    public function activate()
    {
        $this->employee->employeeSalaries()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->is_active = true;
        $this->save();
    }
}