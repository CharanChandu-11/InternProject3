<?php
// app/Http/Controllers/Api/Teacher/LeaveController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveController extends BaseController
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = LeaveApplication::where('user_id', $teacher->id)
            ->with(['leaveType', 'approvedBy']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('from_date')) {
            $query->where('start_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->where('end_date', '<=', $request->to_date);
        }
        
        $leaves = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);
        
        $balance = $this->calculateLeaveBalance($teacher);
        
        return $this->sendResponse([
            'leaves' => $leaves,
            'balance' => $balance,
            'pagination' => [
                'current_page' => $leaves->currentPage(),
                'last_page' => $leaves->lastPage(),
                'per_page' => $leaves->perPage(),
                'total' => $leaves->total(),
            ],
        ], 'Leave applications retrieved');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);
        
        $teacher = Auth::user();
        $leaveType = LeaveType::find($request->leave_type_id);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $balance = $this->calculateLeaveBalance($teacher);
        
        if ($balance['remaining'] < $totalDays) {
            return $this->sendError('Insufficient leave balance', [
                'available' => $balance['remaining'],
                'requested' => $totalDays,
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $leave = LeaveApplication::create([
                'user_id' => $teacher->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);
            
            DB::commit();
            
            return $this->sendResponse($leave, 'Leave application submitted', 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to submit leave: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function show(LeaveApplication $leave)
    {
        $teacher = Auth::user();
        
        if ($leave->user_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $leave->load(['leaveType', 'approvedBy']);
        
        return $this->sendResponse($leave, 'Leave application retrieved');
    }
    
    public function update(Request $request, LeaveApplication $leave)
    {
        $teacher = Auth::user();
        
        if ($leave->user_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        if ($leave->status != 'pending') {
            return $this->sendError('Cannot update leave that is already ' . $leave->status, [], 422);
        }
        
        $request->validate([
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'reason' => 'sometimes|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $updateData = $request->only(['reason']);
            
            if ($request->has('start_date') || $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date ?? $leave->start_date);
                $endDate = Carbon::parse($request->end_date ?? $leave->end_date);
                $updateData['total_days'] = $startDate->diffInDays($endDate) + 1;
                $updateData['start_date'] = $startDate;
                $updateData['end_date'] = $endDate;
            }
            
            $leave->update($updateData);
            
            DB::commit();
            
            return $this->sendResponse($leave, 'Leave application updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update leave: ' . $e->getMessage(), [], 500);
        }
    }
    
    public function destroy(LeaveApplication $leave)
    {
        $teacher = Auth::user();
        
        if ($leave->user_id != $teacher->id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        if ($leave->status != 'pending') {
            return $this->sendError('Cannot cancel leave that is already ' . $leave->status, [], 422);
        }
        
        $leave->update(['status' => 'cancelled']);
        
        return $this->sendResponse([], 'Leave application cancelled');
    }
    
    public function balance()
    {
        $teacher = Auth::user();
        $balance = $this->calculateLeaveBalance($teacher);
        
        return $this->sendResponse($balance, 'Leave balance retrieved');
    }
    
    public function leaveTypes()
    {
        $types = LeaveType::whereIn('applicable_for', ['both', 'teacher'])->get();
        
        return $this->sendResponse($types, 'Leave types retrieved');
    }
    
    private function calculateLeaveBalance($teacher)
    {
        $totalLeaves = 20;
        $usedLeaves = LeaveApplication::where('user_id', $teacher->id)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('total_days');
        
        $pendingLeaves = LeaveApplication::where('user_id', $teacher->id)
            ->where('status', 'pending')
            ->sum('total_days');
        
        return [
            'total' => $totalLeaves,
            'used' => $usedLeaves,
            'pending' => $pendingLeaves,
            'remaining' => $totalLeaves - $usedLeaves,
            'used_percentage' => $totalLeaves > 0 ? round(($usedLeaves / $totalLeaves) * 100, 2) : 0,
        ];
    }
}