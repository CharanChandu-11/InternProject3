<?php
// app/Models/SalaryPayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPayment extends Model
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
        'payment_month',
        'working_days',
        'present_days',
        'leave_days',
        'basic_salary',
        'allowances',
        'deductions',
        'overtime_amount',
        'bonus_amount',
        'net_salary',
        'total_payment',
        'payment_date',
        'payment_method',
        'transaction_id',
        'status',
        'remarks',
        'payslip_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'total_payment' => 'decimal:2',
        'working_days' => 'integer',
        'present_days' => 'integer',
        'leave_days' => 'integer',
    ];

    /**
     * Payment status constants.
     */
    const STATUS_PAID = 'paid';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAILED = 'failed';

    /**
     * Payment method constants.
     */
    const METHOD_CASH = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHEQUE = 'cheque';

    /**
     * Get the employee associated with this salary payment.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the salary template used for this payment.
     */
    public function salaryTemplate()
    {
        return $this->belongsTo(SalaryTemplate::class);
    }

    /**
     * Scope a query to only include payments for a given month.
     */
    public function scopeForMonth($query, $yearMonth)
    {
        return $query->where('payment_month', $yearMonth);
    }

    /**
     * Scope a query to only include payments for a given year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('payment_month', 'like', $year . '%');
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Get the month name from payment_month.
     */
    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->payment_month)->format('F Y');
    }

    /**
     * Get the status color for badges.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the payment method text.
     */
    public function getPaymentMethodTextAttribute(): string
    {
        return match ($this->payment_method) {
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CHEQUE => 'Cheque',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    /**
     * Calculate attendance percentage for the month.
     */
    public function getAttendancePercentageAttribute(): float
    {
        if ($this->working_days <= 0) {
            return 0;
        }
        return round(($this->present_days / $this->working_days) * 100, 2);
    }

    /**
     * Get the formatted net salary.
     */
    public function getNetSalaryFormattedAttribute(): string
    {
        return '₹ ' . number_format($this->net_salary, 2);
    }
}