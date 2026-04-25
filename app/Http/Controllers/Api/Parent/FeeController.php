<?php
// app/Http/Controllers/Api/Parent/FeeController.php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\PaymentResource;
use App\Models\StudentFee;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PDF;

class FeeController extends BaseController
{
    /**
     * Pay fee for a child
     */
    public function pay(Request $request, StudentFee $fee)
    {
        $parent = Auth::user()->parent;
        
        // Verify this fee belongs to parent's child
        $student = $fee->student;
        if (!$parent->children->contains($student)) {
            return $this->sendError('Unauthorized to pay this fee', [], 403);
        }
        
        if ($fee->status == 'paid') {
            return $this->sendError('This fee is already paid', [], 422);
        }
        
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $fee->due_amount,
            'payment_method' => 'required|in:cash,card,bank_transfer,online',
            'transaction_id' => 'nullable|string|max:100',
        ]);
        
        // Create payment record
        $payment = Payment::create([
            'student_id' => $student->id,
            'student_fee_id' => $fee->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'payment_date' => now(),
            'status' => 'completed',
            'received_by' => Auth::id(),
        ]);
        
        // Update fee record
        $newPaidAmount = $fee->paid_amount + $request->amount;
        $newDueAmount = $fee->total_amount - $newPaidAmount;
        
        $status = 'paid';
        if ($newDueAmount > 0) {
            $status = 'partial';
        } elseif ($newDueAmount < 0) {
            $status = 'overpaid';
        }
        
        $fee->update([
            'paid_amount' => $newPaidAmount,
            'due_amount' => $newDueAmount,
            'status' => $status,
        ]);
        
        // Generate receipt number
        $payment->payment_number = 'PAY' . date('Ymd') . str_pad($payment->id, 5, '0', STR_PAD_LEFT);
        $payment->save();
        
        return $this->sendResponse([
            'payment' => new PaymentResource($payment),
            'fee' => [
                'id' => $fee->id,
                'total_amount' => $fee->total_amount,
                'paid_amount' => $fee->paid_amount,
                'due_amount' => $fee->due_amount,
                'status' => $fee->status,
            ],
            'receipt_url' => route('payment.receipt', $payment->id),
        ], 'Payment successful');
    }
    
    /**
     * Get payment receipt
     */
    public function receipt(Payment $payment)
    {
        $parent = Auth::user()->parent;
        
        // Verify this payment belongs to parent's child
        if (!$parent->children->contains($payment->student)) {
            return $this->sendError('Unauthorized to view this receipt', [], 403);
        }
        
        $payment->load(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        
        return $this->sendResponse([
            'payment' => new PaymentResource($payment),
            'student' => [
                'name' => $payment->student->full_name,
                'admission_number' => $payment->student->admission_number,
                'class' => $payment->student->class_name,
                'section' => $payment->student->section_name,
            ],
            'fee' => [
                'category' => $payment->studentFee->feeStructure->feeCategory->name,
                'total_amount' => $payment->studentFee->total_amount,
                'paid_amount' => $payment->studentFee->paid_amount,
                'due_amount' => $payment->studentFee->due_amount,
            ],
        ], 'Receipt retrieved successfully');
    }

        /**
     * Get payment history for all children
     */
    public function paymentHistory(Request $request)
    {
        $parent = Auth::user()->parent;
        $childrenIds = $parent->children->pluck('id');
        
        $query = Payment::whereIn('student_id', $childrenIds)
            ->with(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        
        // Filter by student
        if ($request->filled('student_id')) {
            $studentId = $request->student_id;
            // Verify this student belongs to parent
            if (!$parent->children->contains($studentId)) {
                return $this->sendError('Unauthorized to view this student\'s payments', [], 403);
            }
            $query->where('student_id', $studentId);
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate($request->per_page ?? 20);
        
        // Calculate summary statistics
        $summary = [
            'total_paid' => Payment::whereIn('student_id', $childrenIds)->sum('amount'),
            'total_transactions' => Payment::whereIn('student_id', $childrenIds)->count(),
            'this_month' => Payment::whereIn('student_id', $childrenIds)
                ->whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount'),
            'last_month' => Payment::whereIn('student_id', $childrenIds)
                ->whereMonth('payment_date', Carbon::now()->subMonth()->month)
                ->whereYear('payment_date', Carbon::now()->subMonth()->year)
                ->sum('amount'),
            'by_method' => Payment::whereIn('student_id', $childrenIds)
                ->select('payment_method', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get()
                ->map(function($item) {
                    return [
                        'method' => ucfirst($item->payment_method),
                        'total' => $item->total,
                        'total_formatted' => '₹ ' . number_format($item->total, 2),
                    ];
                }),
            'by_student' => Payment::whereIn('student_id', $childrenIds)
                ->with('student.user')
                ->get()
                ->groupBy('student_id')
                ->map(function($items, $studentId) {
                    $student = $items->first()->student;
                    return [
                        'student_id' => $studentId,
                        'student_name' => $student->user->name,
                        'total_paid' => $items->sum('amount'),
                        'total_paid_formatted' => '₹ ' . number_format($items->sum('amount'), 2),
                        'transactions_count' => $items->count(),
                    ];
                })
                ->values(),
        ];
        
        // Get payment methods for filter
        $paymentMethods = Payment::whereIn('student_id', $childrenIds)
            ->distinct()
            ->pluck('payment_method');
        
        // Get statuses for filter
        $statuses = ['completed', 'pending', 'failed', 'refunded'];
        
        // Format payment data
        $formattedPayments = $payments->getCollection()->map(function($payment) {
            return [
                'id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'student' => [
                    'id' => $payment->student->id,
                    'name' => $payment->student->user->name,
                    'admission_number' => $payment->student->admission_number,
                ],
                'fee_category' => $payment->studentFee->feeStructure->feeCategory->name,
                'amount' => $payment->amount,
                'amount_formatted' => '₹ ' . number_format($payment->amount, 2),
                'payment_method' => $payment->payment_method,
                'payment_method_text' => ucfirst($payment->payment_method),
                'transaction_id' => $payment->transaction_id,
                'payment_date' => $payment->payment_date->toDateString(),
                'payment_date_formatted' => $payment->payment_date->format('d M, Y h:i A'),
                'status' => $payment->status,
                'status_text' => ucfirst($payment->status),
                'status_color' => $this->getStatusColor($payment->status),
                'received_by' => $payment->receivedBy?->name,
                'receipt_url' => route('parent.payments.receipt', $payment),
            ];
        });
        
        // Update the paginated collection with formatted data
        $payments->setCollection($formattedPayments);
        
        return $this->sendResponse([
            'payments' => $payments,
            'summary' => $summary,
            'filters' => [
                'payment_methods' => $paymentMethods,
                'statuses' => $statuses,
            ],
        ], 'Payment history retrieved successfully');
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt(Payment $payment)
    {
        $parent = Auth::user()->parent;
        
        // Verify payment belongs to parent's child
        if (!$parent->children->contains($payment->student)) {
            return $this->sendError('Unauthorized to download this receipt', [], 403);
        }
        
        $payment->load(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        $school = \App\Models\SchoolSetting::first();
        
        $pdf = Pdf::loadView('parent.fees.receipt-pdf', compact('payment', 'school'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('receipt-' . $payment->payment_number . '.pdf');
    }
    
    /**
     * Get payment receipt as JSON (for mobile apps)
     */
    public function getReceiptData(Payment $payment)
    {
        $parent = Auth::user()->parent;
        
        // Verify payment belongs to parent's child
        if (!$parent->children->contains($payment->student)) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $payment->load(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        $school = \App\Models\SchoolSetting::first();
        
        return $this->sendResponse([
            'receipt' => [
                'payment_number' => $payment->payment_number,
                'payment_date' => $payment->payment_date->format('d-m-Y H:i:s'),
                'amount' => $payment->amount,
                'amount_formatted' => '₹ ' . number_format($payment->amount, 2),
                'payment_method' => $payment->payment_method,
                'payment_method_text' => ucfirst($payment->payment_method),
                'transaction_id' => $payment->transaction_id,
                'status' => $payment->status,
            ],
            'student' => [
                'name' => $payment->student->user->name,
                'admission_number' => $payment->student->admission_number,
                'class' => $payment->student->class->name ?? 'N/A',
                'section' => $payment->student->section->name ?? 'N/A',
            ],
            'fee' => [
                'category' => $payment->studentFee->feeStructure->feeCategory->name,
                'total_amount' => $payment->studentFee->total_amount,
                'paid_amount' => $payment->studentFee->paid_amount,
                'due_amount' => $payment->studentFee->due_amount,
            ],
            'school' => [
                'name' => $school->school_name ?? 'Smart School',
                'address' => $school->address ?? '',
                'phone' => $school->phone ?? '',
                'email' => $school->email ?? '',
            ],
            'received_by' => $payment->receivedBy?->name,
        ], 'Receipt data retrieved successfully');
    }
    
    /**
     * Get payment statistics for dashboard
     */
    public function paymentStatistics(Request $request)
    {
        $parent = Auth::user()->parent;
        $childrenIds = $parent->children->pluck('id');
        
        $year = $request->year ?? Carbon::now()->year;
        
        // Monthly payment summary for the year
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $amount = Payment::whereIn('student_id', $childrenIds)
                ->whereMonth('payment_date', $month)
                ->whereYear('payment_date', $year)
                ->sum('amount');
            
            $monthlyData[] = [
                'month' => Carbon::createFromDate($year, $month, 1)->format('M'),
                'amount' => $amount,
                'amount_formatted' => '₹ ' . number_format($amount, 2),
            ];
        }
        
        // Payment method distribution
        $methodDistribution = Payment::whereIn('student_id', $childrenIds)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(function($item) {
                return [
                    'method' => ucfirst($item->payment_method),
                    'count' => $item->count,
                    'total' => $item->total,
                    'total_formatted' => '₹ ' . number_format($item->total, 2),
                    'percentage' => $this->calculatePercentage($item->total, Payment::whereIn('student_id', $this->getChildrenIds())->sum('amount')),
                ];
            });
        
        // Yearly totals
        $yearlyTotals = [];
        for ($i = 3; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $total = Payment::whereIn('student_id', $childrenIds)
                ->whereYear('payment_date', $year)
                ->sum('amount');
            
            $yearlyTotals[] = [
                'year' => $year,
                'total' => $total,
                'total_formatted' => '₹ ' . number_format($total, 2),
            ];
        }
        
        return $this->sendResponse([
            'monthly_data' => $monthlyData,
            'method_distribution' => $methodDistribution,
            'yearly_totals' => $yearlyTotals,
            'total_paid' => Payment::whereIn('student_id', $childrenIds)->sum('amount'),
            'total_transactions' => Payment::whereIn('student_id', $childrenIds)->count(),
        ], 'Payment statistics retrieved successfully');
    }
    
    /**
     * Get recent payments (for dashboard widget)
     */
    public function recentPayments(Request $request)
    {
        $parent = Auth::user()->parent;
        $childrenIds = $parent->children->pluck('id');
        
        $limit = $request->limit ?? 5;
        
        $payments = Payment::whereIn('student_id', $childrenIds)
            ->with(['student.user', 'studentFee.feeStructure.feeCategory'])
            ->orderBy('payment_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'student_name' => $payment->student->user->name,
                    'fee_category' => $payment->studentFee->feeStructure->feeCategory->name,
                    'amount' => $payment->amount,
                    'amount_formatted' => '₹ ' . number_format($payment->amount, 2),
                    'payment_date' => $payment->payment_date->diffForHumans(),
                    'status' => $payment->status,
                    'status_color' => $this->getStatusColor($payment->status),
                ];
            });
        
        return $this->sendResponse($payments, 'Recent payments retrieved successfully');
    }
    
    /**
     * Get children for filter dropdown
     */
    private function getChildrenIds()
    {
        $parent = Auth::user()->parent;
        return $parent->children->pluck('id')->toArray();
    }
    
    private function getStatusColor($status)
    {
        return match($status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'secondary'
        };
    }
    
    private function calculatePercentage($value, $total)
    {
        if ($total == 0) return 0;
        return round(($value / $total) * 100, 2);
    }
}