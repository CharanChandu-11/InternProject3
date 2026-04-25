<?php
// app/Http/Controllers/Api/SuperAdmin/StudentFeeController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\StudentFee;
use Illuminate\Http\Request;

class StudentFeeController extends BaseController
{
    public function index()
    {
        $fees = StudentFee::with(['student.user', 'feeStructure.feeCategory'])->get();
        return $this->sendResponse($fees, 'Student fees retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'total_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'in:pending,partial,paid,overdue',
        ]);
        $validated['paid_amount'] = 0;
        $validated['due_amount'] = $validated['total_amount'];
        $fee = StudentFee::create($validated);
        return $this->sendResponse($fee, 'Student fee created', 201);
    }

    public function show(StudentFee $studentFee)
    {
        $studentFee->load(['student.user', 'feeStructure.feeCategory', 'payments']);
        return $this->sendResponse($studentFee, 'Student fee retrieved');
    }

    public function update(Request $request, StudentFee $studentFee)
    {
        $validated = $request->validate([
            'total_amount' => 'sometimes|numeric|min:0',
            'paid_amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,partial,paid,overdue',
        ]);
        $studentFee->update($validated);
        return $this->sendResponse($studentFee, 'Student fee updated');
    }

    public function destroy(StudentFee $studentFee)
    {
        $studentFee->delete();
        return $this->sendResponse([], 'Student fee deleted');
    }
}