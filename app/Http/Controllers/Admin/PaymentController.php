<?php
// app/Http/Controllers/Admin/PaymentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Classes;
use App\Models\StudentFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['student.user', 'student.class', 'student.section', 'receivedBy']);
        
        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);
        
        // Statistics
        $totalCollected = Payment::where('status', 'completed')->sum('amount');
        $monthlyCollected = Payment::where('status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        $totalTransactions = Payment::count();
        $uniqueStudents = Payment::distinct('student_id')->count();
        
        $classes = Classes::all();
        
        return view('admin.payments.index', compact(
            'payments', 'classes', 'totalCollected', 'monthlyCollected', 
            'totalTransactions', 'uniqueStudents'
        ));
    }
    
    public function create()
    {
        $students = Student::with('user')->get();
        $studentFees = StudentFee::whereIn('status', ['pending', 'partial'])
            ->with(['student.user', 'feeStructure.feeCategory'])
            ->get();
        
        return view('admin.payments.create', compact('students', 'studentFees'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'student_fee_id' => 'nullable|exists:student_fees,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,bank_transfer,online,cheque',
            'transaction_id' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $payment = Payment::create([
                'student_id' => $validated['student_id'],
                'student_fee_id' => $validated['student_fee_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'],
                'payment_date' => $validated['payment_date'],
                'remarks' => $validated['remarks'],
                'status' => 'completed',
                'received_by' => auth()->id(),
            ]);
            
            // Generate payment number
            $payment->payment_number = 'PAY' . date('Ymd') . str_pad($payment->id, 5, '0', STR_PAD_LEFT);
            $payment->save();
            
            // Update student fee if linked
            if ($payment->student_fee_id) {
                $fee = StudentFee::find($payment->student_fee_id);
                $newPaid = $fee->paid_amount + $payment->amount;
                $fee->paid_amount = $newPaid;
                $fee->due_amount = $fee->total_amount - $newPaid;
                $fee->status = $fee->due_amount <= 0 ? 'paid' : 'partial';
                $fee->save();
            }
            
            DB::commit();
            
            return redirect()->route('admin.payments.show', $payment)
                ->with('success', 'Payment recorded successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }
    
    public function show(Payment $payment)
    {
        $payment->load(['student.user', 'student.class', 'student.section', 'receivedBy', 'studentFee.feeStructure.feeCategory']);
        return view('admin.payments.show', compact('payment'));
    }
    
    public function edit(Payment $payment)
    {
        // Only allow editing pending payments
        if ($payment->status != 'pending') {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Cannot edit completed payments.');
        }
        
        $students = Student::with('user')->get();
        $studentFees = StudentFee::whereIn('status', ['pending', 'partial'])
            ->with(['student.user', 'feeStructure.feeCategory'])
            ->get();
        
        return view('admin.payments.edit', compact('payment', 'students', 'studentFees'));
    }
    
    public function update(Request $request, Payment $payment)
    {
        if ($payment->status != 'pending') {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Cannot edit completed payments.');
        }
        
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'student_fee_id' => 'nullable|exists:student_fees,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,bank_transfer,online,cheque',
            'transaction_id' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Revert old fee payment if linked
            if ($payment->student_fee_id) {
                $oldFee = StudentFee::find($payment->student_fee_id);
                if ($oldFee) {
                    $oldFee->paid_amount -= $payment->amount;
                    $oldFee->due_amount = $oldFee->total_amount - $oldFee->paid_amount;
                    $oldFee->status = $oldFee->due_amount <= 0 ? 'paid' : 'partial';
                    $oldFee->save();
                }
            }
            
            // Update payment
            $payment->update($validated);
            
            // Update new fee if linked
            if ($payment->student_fee_id) {
                $newFee = StudentFee::find($payment->student_fee_id);
                if ($newFee) {
                    $newFee->paid_amount += $payment->amount;
                    $newFee->due_amount = $newFee->total_amount - $newFee->paid_amount;
                    $newFee->status = $newFee->due_amount <= 0 ? 'paid' : 'partial';
                    $newFee->save();
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.payments.show', $payment)
                ->with('success', 'Payment updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }
    
    public function destroy(Payment $payment)
    {
        if ($payment->status != 'pending') {
            return redirect()->route('admin.payments.index')
                ->with('error', 'Cannot delete completed payments.');
        }
        
        DB::beginTransaction();
        
        try {
            // Revert fee payment if linked
            if ($payment->student_fee_id) {
                $fee = StudentFee::find($payment->student_fee_id);
                if ($fee) {
                    $fee->paid_amount -= $payment->amount;
                    $fee->due_amount = $fee->total_amount - $fee->paid_amount;
                    $fee->status = $fee->due_amount <= 0 ? 'paid' : 'partial';
                    $fee->save();
                }
            }
            
            $payment->delete();
            
            DB::commit();
            
            return redirect()->route('admin.payments.index')
                ->with('success', 'Payment deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }
    
    public function receipt(Payment $payment)
    {
        $payment->load(['student.user', 'student.class', 'student.section', 'receivedBy', 'studentFee.feeStructure.feeCategory']);
        
        $pdf = PDF::loadView('admin.payments.receipt-pdf', compact('payment'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('receipt-' . $payment->payment_number . '.pdf');
    }
}