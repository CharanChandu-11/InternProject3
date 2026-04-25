<?php
// app/Http/Controllers/Api/SuperAdmin/PaymentController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function index()
    {
        $payments = Payment::with(['student.user', 'receivedBy', 'studentFee'])->get();
        return $this->sendResponse($payments, 'Payments retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,online',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        $validated['received_by'] = auth()->id();
        $validated['status'] = 'completed';
        $payment = Payment::create($validated);

        // Update student fee
        $fee = $payment->studentFee;
        $fee->paid_amount += $payment->amount;
        $fee->due_amount = $fee->total_amount - $fee->paid_amount;
        $fee->status = $fee->due_amount <= 0 ? 'paid' : ($fee->due_amount < $fee->total_amount ? 'partial' : $fee->status);
        $fee->save();

        return $this->sendResponse($payment, 'Payment recorded', 201);
    }

    public function show(Payment $payment)
    {
        $payment->load(['student.user', 'receivedBy', 'studentFee']);
        return $this->sendResponse($payment, 'Payment retrieved');
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_method' => 'sometimes|in:cash,card,bank_transfer,online',
            'transaction_id' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);
        $payment->update($validated);
        return $this->sendResponse($payment, 'Payment updated');
    }

    public function destroy(Payment $payment)
    {
        // Adjust student fee before deletion
        $fee = $payment->studentFee;
        $fee->paid_amount -= $payment->amount;
        $fee->due_amount = $fee->total_amount - $fee->paid_amount;
        $fee->status = $fee->due_amount <= 0 ? 'paid' : ($fee->due_amount < $fee->total_amount ? 'partial' : 'pending');
        $fee->save();

        $payment->delete();
        return $this->sendResponse([], 'Payment deleted');
    }

    public function receipt(Payment $payment)
    {
        // Generate receipt URL or data
        $receipt = [
            'payment' => $payment,
            'student' => $payment->student,
            'fee' => $payment->studentFee,
            'school' => \App\Models\SchoolSetting::first(),
        ];
        return $this->sendResponse($receipt, 'Receipt data');
    }
}