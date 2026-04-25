<?php
// app/Http/Controllers/Api/Employee/SalaryController.php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Api\BaseController;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryController extends BaseController
{
    /**
     * Get salary history
     */
    public function index(Request $request)
    {
        $employee = Auth::user()->employee;
        
        $query = SalaryPayment::where('employee_id', $employee->id)
            ->with(['salaryTemplate']);
        
        // Filter by year
        if ($request->has('year')) {
            $query->where('payment_month', 'like', $request->year . '%');
        }
        
        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate($request->per_page ?? 12);
        
        // Summary
        $yearlySummary = [];
        $payments->getCollection()->groupBy(function($payment) {
            return substr($payment->payment_month, 0, 4);
        })->each(function($yearPayments, $year) use (&$yearlySummary) {
            $yearlySummary[] = [
                'year' => $year,
                'total_earned' => $yearPayments->sum('net_salary'),
                'total_earned_formatted' => '₹ ' . number_format($yearPayments->sum('net_salary'), 2),
                'total_deductions' => $yearPayments->sum('deductions'),
                'total_deductions_formatted' => '₹ ' . number_format($yearPayments->sum('deductions'), 2),
                'months' => $yearPayments->count(),
            ];
        });
        
        return $this->sendResponse([
            'salary_payments' => $payments->items(),
            'yearly_summary' => $yearlySummary,
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ], 'Salary history retrieved successfully');
    }
    
    /**
     * Get specific salary payment
     */
    public function show(SalaryPayment $payment)
    {
        $employee = Auth::user()->employee;
        
        if ($payment->employee_id != $employee->id) {
            return $this->sendError('Unauthorized to view this salary payment', [], 403);
        }
        
        $payment->load(['salaryTemplate']);
        
        // Calculate breakdown
        $breakdown = [
            'earnings' => [],
            'deductions' => [],
        ];
        
        if ($payment->basic_salary > 0) {
            $breakdown['earnings'][] = [
                'component' => 'Basic Salary',
                'amount' => $payment->basic_salary,
                'amount_formatted' => '₹ ' . number_format($payment->basic_salary, 2),
            ];
        }
        
        if ($payment->allowances > 0) {
            $breakdown['earnings'][] = [
                'component' => 'Allowances',
                'amount' => $payment->allowances,
                'amount_formatted' => '₹ ' . number_format($payment->allowances, 2),
            ];
        }
        
        if ($payment->overtime_amount > 0) {
            $breakdown['earnings'][] = [
                'component' => 'Overtime',
                'amount' => $payment->overtime_amount,
                'amount_formatted' => '₹ ' . number_format($payment->overtime_amount, 2),
            ];
        }
        
        if ($payment->bonus_amount > 0) {
            $breakdown['earnings'][] = [
                'component' => 'Bonus',
                'amount' => $payment->bonus_amount,
                'amount_formatted' => '₹ ' . number_format($payment->bonus_amount, 2),
            ];
        }
        
        if ($payment->deductions > 0) {
            $breakdown['deductions'][] = [
                'component' => 'Total Deductions',
                'amount' => $payment->deductions,
                'amount_formatted' => '₹ ' . number_format($payment->deductions, 2),
            ];
        }
        
        return $this->sendResponse([
            'payment' => [
                'id' => $payment->id,
                'month' => $payment->payment_month,
                'month_formatted' => \Carbon\Carbon::createFromFormat('Y-m', $payment->payment_month)->format('F Y'),
                'payment_date' => $payment->payment_date->format('F j, Y'),
                'payment_method' => $payment->payment_method,
                'payment_method_text' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
                'status_color' => $payment->status == 'paid' ? 'success' : 'warning',
            ],
            'attendance_summary' => [
                'working_days' => $payment->working_days,
                'present_days' => $payment->present_days,
                'leave_days' => $payment->leave_days,
                'attendance_percentage' => $payment->working_days > 0 
                    ? round(($payment->present_days / $payment->working_days) * 100, 2) 
                    : 0,
            ],
            'breakdown' => $breakdown,
            'total' => [
                'gross_salary' => $payment->basic_salary + $payment->allowances + $payment->overtime_amount + $payment->bonus_amount,
                'deductions' => $payment->deductions,
                'net_salary' => $payment->net_salary,
                'net_salary_formatted' => '₹ ' . number_format($payment->net_salary, 2),
            ],
        ], 'Salary payment details retrieved successfully');
    }
    
    /**
     * Get salary slip
     */
    public function payslip(SalaryPayment $payment)
    {
        $employee = Auth::user()->employee;
        
        if ($payment->employee_id != $employee->id) {
            return $this->sendError('Unauthorized to view this payslip', [], 403);
        }
        
        $payment->load(['salaryTemplate', 'employee.user']);
        
        $data = [
            'employee' => [
                'name' => $payment->employee->user->name,
                'employee_id' => $payment->employee->employee_id,
                'department' => $payment->employee->department,
                'designation' => $payment->employee->designation,
                'joining_date' => $payment->employee->joining_date->format('F j, Y'),
            ],
            'payment' => [
                'month' => \Carbon\Carbon::createFromFormat('Y-m', $payment->payment_month)->format('F Y'),
                'payment_date' => $payment->payment_date->format('F j, Y'),
                'payment_method' => $payment->payment_method,
            ],
            'attendance' => [
                'working_days' => $payment->working_days,
                'present_days' => $payment->present_days,
                'leave_days' => $payment->leave_days,
            ],
            'earnings' => [
                'basic_salary' => $payment->basic_salary,
                'allowances' => $payment->allowances,
                'overtime' => $payment->overtime_amount,
                'bonus' => $payment->bonus_amount,
                'total_earnings' => $payment->basic_salary + $payment->allowances + $payment->overtime_amount + $payment->bonus_amount,
            ],
            'deductions' => [
                'total_deductions' => $payment->deductions,
            ],
            'net_salary' => $payment->net_salary,
        ];
        
        return $this->sendResponse($data, 'Payslip retrieved successfully');
    }
    
    /**
     * Get salary summary by year
     */
    public function yearlySummary(Request $request)
    {
        $employee = Auth::user()->employee;
        $year = $request->year ?? now()->year;
        
        $payments = SalaryPayment::where('employee_id', $employee->id)
            ->where('payment_month', 'like', $year . '%')
            ->where('status', 'paid')
            ->get();
        
        $monthly = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthPayment = $payments->firstWhere('payment_month', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));
            $monthly[] = [
                'month' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('M'),
                'amount' => $monthPayment ? $monthPayment->net_salary : 0,
                'amount_formatted' => $monthPayment ? '₹ ' . number_format($monthPayment->net_salary, 2) : '₹ 0.00',
                'status' => $monthPayment ? 'paid' : 'pending',
            ];
        }
        
        return $this->sendResponse([
            'year' => $year,
            'total_earned' => $payments->sum('net_salary'),
            'total_earned_formatted' => '₹ ' . number_format($payments->sum('net_salary'), 2),
            'total_payments' => $payments->count(),
            'monthly_breakdown' => $monthly,
        ], 'Yearly salary summary retrieved successfully');
    }
}