<?php
// app/Http/Controllers/Student/FeeController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentFee;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeController extends Controller
{
    /**
     * Display all fees for the student
     */
    public function index()
    {
        $student = Auth::user()->student;
        
        $fees = StudentFee::where('student_id', $student->id)
            ->with(['feeStructure.feeCategory'])
            ->orderBy('due_date')
            ->get();
        
        // Calculate summary
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
        
        // Get recent payments
        $recentPayments = Payment::where('student_id', $student->id)
            ->with('receivedBy')
            ->latest()
            ->take(5)
            ->get();
        
        return view('student.fees.index', compact('fees', 'summary', 'recentPayments'));
    }
    
    /**
     * Show specific fee details
     */
    public function show(StudentFee $fee)
    {
        $student = Auth::user()->student;
        
        // Verify fee belongs to student
        if ($fee->student_id != $student->id) {
            abort(404, 'Fee record not found.');
        }
        
        $fee->load(['feeStructure.feeCategory']);
        
        $payments = Payment::where('student_fee_id', $fee->id)
            ->with('receivedBy')
            ->orderBy('payment_date', 'desc')
            ->get();
        
        $canPay = $fee->status != 'paid' && $fee->due_amount > 0;
        
        return view('student.fees.show', compact('fee', 'payments', 'canPay'));
    }
    
    /**
     * Process fee payment
     */
    public function pay(Request $request, StudentFee $fee)
    {
        $student = Auth::user()->student;
        
        // Verify fee belongs to student
        if ($fee->student_id != $student->id) {
            return response()->json(['error' => 'Fee record not found.'], 404);
        }
        
        if ($fee->status == 'paid' || $fee->due_amount <= 0) {
            return redirect()->back()->with('error', 'This fee is already fully paid.');
        }
        
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $fee->due_amount,
            'payment_method' => 'required|in:cash,card,bank_transfer,online',
            'transaction_id' => 'nullable|string|max:100',
        ]);
        
        DB::beginTransaction();
        
        try {
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
                'payment_number' => $this->generatePaymentNumber(),
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
            
            DB::commit();
            
            // Send notification (optional)
            // Notification::send(...)
            
            return redirect()->route('student.fees.show', $fee)
                ->with('success', 'Payment of ₹' . number_format($request->amount, 2) . ' completed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
    
    /**
     * View payment history
     */
    public function paymentHistory(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = Payment::where('student_id', $student->id)
            ->with(['studentFee.feeStructure.feeCategory', 'receivedBy']);
        
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
        
        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate(15)
            ->appends($request->query());
        
        // Summary
        $summary = [
            'total_paid' => Payment::where('student_id', $student->id)->sum('amount'),
            'total_transactions' => Payment::where('student_id', $student->id)->count(),
            'this_month' => Payment::where('student_id', $student->id)
                ->whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount'),
            'last_payment' => Payment::where('student_id', $student->id)
                ->latest()
                ->first(),
        ];
        
        return view('student.fees.history', compact('payments', 'summary'));
    }
    
    /**
     * Download payment receipt
     */
    public function downloadReceipt(Payment $payment)
    {
        $student = Auth::user()->student;
        
        // Verify payment belongs to student
        if ($payment->student_id != $student->id) {
            abort(404);
        }
        
        $payment->load(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.fees.receipt', compact('payment'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('receipt-' . $payment->payment_number . '.pdf');
    }
    
    /**
     * Generate unique payment number
     */
    private function generatePaymentNumber()
    {
        $prefix = 'PAY';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        return $prefix . $date . $random;
    }
    
}