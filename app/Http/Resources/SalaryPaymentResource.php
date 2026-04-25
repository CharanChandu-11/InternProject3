<?php
// app/Http/Resources/SalaryPaymentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'salary_template' => new SalaryTemplateResource($this->whenLoaded('salaryTemplate')),
            'payment_month' => $this->payment_month,
            'payment_month_formatted' => \Carbon\Carbon::createFromFormat('Y-m', $this->payment_month)?->format('F Y'),
            'working_days' => $this->working_days,
            'present_days' => $this->present_days,
            'leave_days' => $this->leave_days,
            'absent_days' => $this->working_days - $this->present_days - $this->leave_days,
            'attendance_percentage' => $this->working_days > 0 
                ? round(($this->present_days / $this->working_days) * 100, 2) 
                : 0,
            
            // Earnings
            'basic_salary' => $this->basic_salary,
            'basic_salary_formatted' => '₹ ' . number_format($this->basic_salary, 2),
            'allowances' => $this->allowances,
            'allowances_formatted' => '₹ ' . number_format($this->allowances, 2),
            'overtime_amount' => $this->overtime_amount,
            'overtime_amount_formatted' => '₹ ' . number_format($this->overtime_amount, 2),
            'bonus_amount' => $this->bonus_amount,
            'bonus_amount_formatted' => '₹ ' . number_format($this->bonus_amount, 2),
            'total_earnings' => $this->basic_salary + $this->allowances + $this->overtime_amount + $this->bonus_amount,
            'total_earnings_formatted' => '₹ ' . number_format($this->basic_salary + $this->allowances + $this->overtime_amount + $this->bonus_amount, 2),
            
            // Deductions
            'deductions' => $this->deductions,
            'deductions_formatted' => '₹ ' . number_format($this->deductions, 2),
            'net_salary' => $this->net_salary,
            'net_salary_formatted' => '₹ ' . number_format($this->net_salary, 2),
            'total_payment' => $this->total_payment,
            'total_payment_formatted' => '₹ ' . number_format($this->total_payment, 2),
            
            'payment_date' => $this->payment_date?->toDateString(),
            'payment_date_formatted' => $this->payment_date?->format('F j, Y'),
            'payment_method' => $this->payment_method,
            'payment_method_text' => ucfirst(str_replace('_', ' ', $this->payment_method)),
            'payment_method_icon' => $this->getPaymentMethodIcon(),
            'transaction_id' => $this->transaction_id,
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'remarks' => $this->remarks,
            'payslip_url' => $this->payslip_url,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function getPaymentMethodIcon()
    {
        return match($this->payment_method) {
            'cash' => 'fas fa-money-bill-wave',
            'bank_transfer' => 'fas fa-university',
            'cheque' => 'fas fa-money-check',
            default => 'fas fa-credit-card'
        };
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'paid' => 'success',
            'pending' => 'warning',
            'processing' => 'info',
            'failed' => 'danger',
            default => 'secondary'
        };
    }
}