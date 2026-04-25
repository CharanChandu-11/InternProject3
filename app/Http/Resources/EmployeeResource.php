<?php
// app/Http/Resources/EmployeeResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'employment_type' => $this->employment_type,
            'employment_type_text' => $this->getEmploymentTypeText(),
            'employment_type_badge' => $this->getEmploymentTypeBadge(),
            'joining_date' => $this->joining_date?->toDateString(),
            'joining_date_formatted' => $this->joining_date?->format('F j, Y'),
            'department' => $this->department,
            'department_text' => ucwords(str_replace('_', ' ', $this->department)),
            'designation' => $this->designation,
            'salary' => $this->salary,
            'salary_formatted' => $this->salary ? '₹ ' . number_format($this->salary, 2) : null,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->maskBankAccount(),
            'ifsc_code' => $this->ifsc_code,
            'pan_number' => $this->maskPanNumber(),
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'experience_text' => $this->experience_years ? $this->experience_years . ' years' : 'Fresher',
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->user?->address,
            'profile_photo' => $this->user?->profile_photo_url,
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
            'leave_applications' => LeaveApplicationResource::collection($this->whenLoaded('leaveApplications')),
            'salary_payments' => SalaryPaymentResource::collection($this->whenLoaded('salaryPayments')),
            'employee_salaries' => EmployeeSalaryResource::collection($this->whenLoaded('employeeSalaries')),
            'attendance_percentage' => $this->attendance_percentage,
            'leave_balance' => $this->getLeaveBalance(),
            'years_of_service' => $this->getYearsOfService(),
            'is_probation_completed' => $this->isProbationCompleted(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getEmploymentTypeText()
    {
        $types = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'probation' => 'Probation',
            'intern' => 'Intern',
            'temporary' => 'Temporary'
        ];

        return $types[$this->employment_type] ?? ucfirst(str_replace('_', ' ', $this->employment_type));
    }

    private function getEmploymentTypeBadge()
    {
        $badges = [
            'full_time' => 'success',
            'part_time' => 'info',
            'contract' => 'warning',
            'probation' => 'primary',
            'intern' => 'secondary',
            'temporary' => 'dark'
        ];

        return [
            'text' => $this->getEmploymentTypeText(),
            'color' => $badges[$this->employment_type] ?? 'secondary'
        ];
    }

    private function maskBankAccount()
    {
        if (!$this->bank_account) {
            return null;
        }

        $length = strlen($this->bank_account);
        if ($length <= 4) {
            return str_repeat('X', $length);
        }

        return str_repeat('X', $length - 4) . substr($this->bank_account, -4);
    }

    private function maskPanNumber()
    {
        if (!$this->pan_number) {
            return null;
        }

        return 'XXXXX' . substr($this->pan_number, 5);
    }

    private function getLeaveBalance()
    {
        if (!$this->relationLoaded('leaveApplications')) {
            return null;
        }

        $totalLeaves = 20; // Configure based on employment type
        $usedLeaves = $this->leaveApplications
            ->where('status', 'approved')
            ->whereYear('created_at', now()->year)
            ->sum('total_days');

        return [
            'total' => $totalLeaves,
            'used' => $usedLeaves,
            'remaining' => $totalLeaves - $usedLeaves,
            'used_percentage' => $totalLeaves > 0 ? round(($usedLeaves / $totalLeaves) * 100, 2) : 0
        ];
    }

    private function getYearsOfService()
    {
        if (!$this->joining_date) {
            return null;
        }

        $years = $this->joining_date->diffInYears(now());
        $months = $this->joining_date->diffInMonths(now()) % 12;

        if ($years == 0) {
            return $months . ' ' . ($months == 1 ? 'month' : 'months');
        }

        return $years . ' ' . ($years == 1 ? 'year' : 'years') . 
               ($months > 0 ? ' ' . $months . ' ' . ($months == 1 ? 'month' : 'months') : '');
    }

    private function isProbationCompleted()
    {
        if ($this->employment_type !== 'probation' || !$this->joining_date) {
            return null;
        }

        $probationPeriod = 6; // 6 months probation
        $completionDate = $this->joining_date->addMonths($probationPeriod);
        
        return [
            'completed' => now()->greaterThanOrEqualTo($completionDate),
            'completion_date' => $completionDate->toDateString(),
            'days_remaining' => now()->diffInDays($completionDate, false)
        ];
    }
}