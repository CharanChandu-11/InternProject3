<?php
// app/Http/Controllers/Parent/FeeController.php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\StudentFee;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    public function payForm(StudentFee $fee)
    {
        $parent = Auth::user()->parent;
        $student = $fee->student;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        if ($fee->status == 'paid' || $fee->due_amount <= 0) {
            return redirect()->route('parent.children.fees', $student)
                ->with('error', 'This fee is already paid.');
        }
        
        return view('parent.fees.pay', compact('fee', 'student'));
    }
    
    public function processPayment(Request $request, StudentFee $fee)
    {
        $parent = Auth::user()->parent;
        $student = $fee->student;
        
        if (!$parent->children->contains($student)) {
            abort(403);
        }
        
        if ($fee->status == 'paid' || $fee->due_amount <= 0) {
            return redirect()->route('parent.children.fees', $student)
                ->with('error', 'This fee is already paid.');
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
            
            $status = 'paid';
            if ($newDue > 0) {
                $status = 'partial';
            }
            
            $fee->update([
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'status' => $status,
            ]);
            
            DB::commit();
            
            return redirect()->route('parent.children.fees', $student)
                ->with('success', 'Payment of ₹' . number_format($request->amount, 2) . ' completed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
    
    public function paymentHistory(Request $request)
    {
        $parent = Auth::user()->parent;
        $childrenIds = $parent->children->pluck('id');
        
        $query = Payment::whereIn('student_id', $childrenIds)
            ->with(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        
        $payments = $query->orderBy('payment_date', 'desc')
            ->paginate(20)
            ->appends($request->query());
        
        $students = $parent->children;
        
        return view('parent.fees.history', compact('payments', 'students'));
    }
    
    public function downloadReceipt(Payment $payment)
    {
        $parent = Auth::user()->parent;
        
        if (!$parent->children->contains($payment->student)) {
            abort(404);
        }
        
        $payment->load(['student.user', 'studentFee.feeStructure.feeCategory', 'receivedBy']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('parent.fees.receipt', compact('payment'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('receipt-' . $payment->payment_number . '.pdf');
    }
    
    private function generatePaymentNumber()
    {
        return 'PAY' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }
}