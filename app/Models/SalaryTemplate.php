<?php
// app/Models/SalaryTemplate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'basic_salary',
        'house_rent_allowance',
        'dearness_allowance',
        'travel_allowance',
        'medical_allowance',
        'special_allowance',
        'provident_fund',
        'professional_tax',
        'income_tax',
        'total_earnings',
        'total_deductions',
        'net_salary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'basic_salary' => 'decimal:2',
        'house_rent_allowance' => 'decimal:2',
        'dearness_allowance' => 'decimal:2',
        'travel_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'provident_fund' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * Get the employee salaries associated with this template.
     */
    public function employeeSalaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    /**
     * Calculate total earnings based on components.
     */
    public function calculateTotalEarnings(): float
    {
        return $this->basic_salary
            + ($this->house_rent_allowance ?? 0)
            + ($this->dearness_allowance ?? 0)
            + ($this->travel_allowance ?? 0)
            + ($this->medical_allowance ?? 0)
            + ($this->special_allowance ?? 0);
    }

    /**
     * Calculate total deductions based on components.
     */
    public function calculateTotalDeductions(): float
    {
        return ($this->provident_fund ?? 0)
            + ($this->professional_tax ?? 0)
            + ($this->income_tax ?? 0);
    }

    /**
     * Calculate net salary.
     */
    public function calculateNetSalary(): float
    {
        return $this->calculateTotalEarnings() - $this->calculateTotalDeductions();
    }

    /**
     * Boot the model to automatically calculate totals on save.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($template) {
            $template->total_earnings = $template->calculateTotalEarnings();
            $template->total_deductions = $template->calculateTotalDeductions();
            $template->net_salary = $template->calculateNetSalary();
        });
    }
}