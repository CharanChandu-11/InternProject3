<?php
// app/Http/Resources/SalaryTemplateResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            
            // Earnings
            'basic_salary' => $this->basic_salary,
            'basic_salary_formatted' => '₹ ' . number_format($this->basic_salary, 2),
            'house_rent_allowance' => $this->house_rent_allowance,
            'house_rent_allowance_formatted' => '₹ ' . number_format($this->house_rent_allowance, 2),
            'dearness_allowance' => $this->dearness_allowance,
            'dearness_allowance_formatted' => '₹ ' . number_format($this->dearness_allowance, 2),
            'travel_allowance' => $this->travel_allowance,
            'travel_allowance_formatted' => '₹ ' . number_format($this->travel_allowance, 2),
            'medical_allowance' => $this->medical_allowance,
            'medical_allowance_formatted' => '₹ ' . number_format($this->medical_allowance, 2),
            'special_allowance' => $this->special_allowance,
            'special_allowance_formatted' => '₹ ' . number_format($this->special_allowance, 2),
            'total_earnings' => $this->total_earnings,
            'total_earnings_formatted' => '₹ ' . number_format($this->total_earnings, 2),
            
            // Deductions
            'provident_fund' => $this->provident_fund,
            'provident_fund_formatted' => '₹ ' . number_format($this->provident_fund, 2),
            'professional_tax' => $this->professional_tax,
            'professional_tax_formatted' => '₹ ' . number_format($this->professional_tax, 2),
            'income_tax' => $this->income_tax,
            'income_tax_formatted' => '₹ ' . number_format($this->income_tax, 2),
            'total_deductions' => $this->total_deductions,
            'total_deductions_formatted' => '₹ ' . number_format($this->total_deductions, 2),
            
            'net_salary' => $this->net_salary,
            'net_salary_formatted' => '₹ ' . number_format($this->net_salary, 2),
            
            'breakdown' => $this->getBreakdown(),
            'employee_salaries_count' => $this->whenCounted('employeeSalaries'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function getBreakdown()
    {
        $earnings = [];
        $deductions = [];

        if ($this->basic_salary > 0) {
            $earnings[] = [
                'component' => 'Basic Salary',
                'amount' => $this->basic_salary,
                'percentage' => round(($this->basic_salary / $this->total_earnings) * 100, 2)
            ];
        }

        if ($this->house_rent_allowance > 0) {
            $earnings[] = [
                'component' => 'House Rent Allowance',
                'amount' => $this->house_rent_allowance,
                'percentage' => round(($this->house_rent_allowance / $this->total_earnings) * 100, 2)
            ];
        }

        if ($this->dearness_allowance > 0) {
            $earnings[] = [
                'component' => 'Dearness Allowance',
                'amount' => $this->dearness_allowance,
                'percentage' => round(($this->dearness_allowance / $this->total_earnings) * 100, 2)
            ];
        }

        if ($this->travel_allowance > 0) {
            $earnings[] = [
                'component' => 'Travel Allowance',
                'amount' => $this->travel_allowance,
                'percentage' => round(($this->travel_allowance / $this->total_earnings) * 100, 2)
            ];
        }

        if ($this->medical_allowance > 0) {
            $earnings[] = [
                'component' => 'Medical Allowance',
                'amount' => $this->medical_allowance,
                'percentage' => round(($this->medical_allowance / $this->total_earnings) * 100, 2)
            ];
        }

        if ($this->special_allowance > 0) {
            $earnings[] = [
                'component' => 'Special Allowance',
                'amount' => $this->special_allowance,
                'percentage' => round(($this->special_allowance / $this->total_earnings) * 100, 2)
            ];
        }

        if ($this->provident_fund > 0) {
            $deductions[] = [
                'component' => 'Provident Fund',
                'amount' => $this->provident_fund,
                'percentage' => round(($this->provident_fund / $this->total_deductions) * 100, 2)
            ];
        }

        if ($this->professional_tax > 0) {
            $deductions[] = [
                'component' => 'Professional Tax',
                'amount' => $this->professional_tax,
                'percentage' => round(($this->professional_tax / $this->total_deductions) * 100, 2)
            ];
        }

        if ($this->income_tax > 0) {
            $deductions[] = [
                'component' => 'Income Tax',
                'amount' => $this->income_tax,
                'percentage' => round(($this->income_tax / $this->total_deductions) * 100, 2)
            ];
        }

        return [
            'earnings' => $earnings,
            'deductions' => $deductions
        ];
    }
}