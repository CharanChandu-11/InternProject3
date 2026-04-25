<?php
// app/Http/Controllers/Api/Student/FeeController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\StudentFee;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeController extends BaseController
{
    /**
     * Get all fees for the student with summary
     */
    public function index()
    {
        $student = Auth::user()->student;

        $fees = StudentFee::where('student_id', $student->id)
            ->with(['feeStructure.feeCategory'])
            ->orderBy('due_date')
            ->get();

        $summary = [
            'total_fees' => $fees->sum('total_amount'),
            'total_paid' => $fees->sum('paid_amount'),
            'total_due' => $fees->sum('due_amount'),
            'paid_percentage' => $fees->sum('total_amount') > 0
                ? round(($fees->sum('paid_amount') / $fees->sum('total_amount')) * 100, 2)
                : 0,
            'overdue_count' => $fees->where('status', 'overdue')->count(),
            'pending_count' => $fees->whereIn('status', ['pending', 'partial'])->count(),
            'paid_count' => $fees->where('status', 'paid')->count(),
        ];

        $recentPayments = Payment::where('student_id', $student->id)
            ->with('receivedBy')
            ->latest()
            ->take(5)
            ->get();

        return $this->sendResponse([
            'fees' => $fees,
            'summary' => $summary,
            'recent_payments' => $recentPayments,
        ], 'Fee details retrieved');
    }

    /**
     * Show a specific fee with payment history
     */
    public function show(StudentFee $fee)
    {
        $student = Auth::user()->student;
        if ($fee->student_id != $student->id) {
            return $this->sendError('Fee record not found', [], 404);
        }

        $fee->load(['feeStructure.feeCategory']);
        $payments = Payment::where('student_fee_id', $fee->id)
            ->with('receivedBy')
            ->orderBy('payment_date', 'desc')
            ->get();

        return $this->sendResponse([
            'fee' => $fee,
            'payments' => $payments,
            'can_pay' => $fee->status != 'paid' && $fee->due_amount > 0,
        ], 'Fee details retrieved');
    }

    /**
     * Process a payment for a fee
     */
    public function pay(Request $request, StudentFee $fee)
    {
        $student = Auth::user()->student;
        if ($fee->student_id != $student->id) {
            return $this->sendError('Fee record not found', [], 404);
        }
        if ($fee->status == 'paid' || $fee->due_amount <= 0) {
            return $this->sendError('This fee is already fully paid', [], 422);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $fee->due_amount,
            'payment_method' => 'required|in:cash,card,bank_transfer,online',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'student_id' => $student->id,
                'student_fee_id' => $fee->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'payment_date' => now(),
                'status' => 'completed',
                'received_by' => Auth::id(),
                'payment_number' => $this->generatePaymentNumber(),
            ]);

            $newPaid = $fee->paid_amount + $request->amount;
            $newDue = $fee->total_amount - $newPaid;
            $status = $newDue <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'pending');
            $fee->update([
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'status' => $status,
            ]);

            DB::commit();
            return $this->sendResponse($payment, 'Payment successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Payment failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Get payment history with filters
     */
    public function paymentHistory(Request $request)
    {
        $student = Auth::user()->student;

        $query = Payment::where('student_id', $student->id)
            ->with(['studentFee.feeStructure.feeCategory', 'receivedBy']);

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate($request->per_page ?? 15);

        $summary = [
            'total_paid' => Payment::where('student_id', $student->id)->sum('amount'),
            'total_transactions' => Payment::where('student_id', $student->id)->count(),
            'this_month' => Payment::where('student_id', $student->id)
                ->whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount'),
            'last_payment' => Payment::where('student_id', $student->id)->latest()->first(),
        ];

        return $this->sendResponse([
            'payments' => $payments->items(),
            'summary' => $summary,
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ], 'Payment history retrieved');
    }

    /**
     * Download payment receipt (returns URL or file)
     */
    public function downloadReceipt(Payment $payment)
    {
        $student = Auth::user()->student;
        if ($payment->student_id != $student->id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        // Generate PDF and return download response
        $payment->load(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.fees.receipt', compact('payment'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('receipt-' . $payment->payment_number . '.pdf');
    }

    /**
     * Generate a unique payment number
     */
    private function generatePaymentNumber()
    {
        return 'PAY' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }
}