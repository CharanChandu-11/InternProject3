<?php
// app/Http/Controllers/Api/Employee/LeaveController.php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\LeaveApplicationResource;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveController extends BaseController
{
    /**
     * Display a listing of leave applications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = LeaveApplication::where('user_id', $user->id)
            ->with(['leaveType', 'approvedBy']);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('start_date', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->where('end_date', '<=', $request->to_date);
        }
        
        $leaves = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        $leaveBalance = $this->calculateLeaveBalance();
        
        return $this->sendResponse([
            'leaves' => LeaveApplicationResource::collection($leaves),
            'balance' => $leaveBalance,
            'pagination' => [
                'current_page' => $leaves->currentPage(),
                'last_page' => $leaves->lastPage(),
                'per_page' => $leaves->perPage(),
                'total' => $leaves->total(),
            ],
        ], 'Leave applications retrieved successfully');
    }
    
    /**
     * Store a newly created leave application
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);
        
        $user = Auth::user();
        $leaveType = LeaveType::find($request->leave_type_id);
        
        // Check if leave type is applicable for employee
        if (!in_array($leaveType->applicable_for, ['both', 'employee'])) {
            return $this->sendError('This leave type is not applicable for employees', [], 422);
        }
        
        // Calculate days (excluding weekends if configured)
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $this->calculateWorkingDays($startDate, $endDate);
        
        // Check leave balance
        $balance = $this->calculateLeaveBalance();
        
        if ($balance['remaining'] < $totalDays) {
            return $this->sendError('Insufficient leave balance', [
                'available' => $balance['remaining'],
                'requested' => $totalDays,
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $leave = LeaveApplication::create([
                'user_id' => $user->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);
            
            DB::commit();
            
            // Notify admin/HR
            $this->notifyAdmin($leave);
            
            return $this->sendResponse(
                new LeaveApplicationResource($leave->load('leaveType')),
                'Leave application submitted successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to submit leave application: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Display the specified leave application
     */
    public function show(LeaveApplication $leave)
    {
        $user = Auth::user();
        
        if ($leave->user_id != $user->id) {
            return $this->sendError('Unauthorized to view this leave application', [], 403);
        }
        
        $leave->load(['leaveType', 'approvedBy']);
        
        return $this->sendResponse(
            new LeaveApplicationResource($leave),
            'Leave application retrieved successfully'
        );
    }
    
    /**
     * Update the specified leave application
     */
    public function update(Request $request, LeaveApplication $leave)
    {
        $user = Auth::user();
        
        if ($leave->user_id != $user->id) {
            return $this->sendError('Unauthorized to update this leave application', [], 403);
        }
        
        if ($leave->status != 'pending') {
            return $this->sendError('Cannot update leave application that is already ' . $leave->status, [], 422);
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
                $updateData['total_days'] = $this->calculateWorkingDays($startDate, $endDate);
                $updateData['start_date'] = $startDate;
                $updateData['end_date'] = $endDate;
            }
            
            $leave->update($updateData);
            
            DB::commit();
            
            return $this->sendResponse(
                new LeaveApplicationResource($leave->load('leaveType')),
                'Leave application updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update leave application: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Cancel the specified leave application
     */
    public function destroy(LeaveApplication $leave)
    {
        $user = Auth::user();
        
        if ($leave->user_id != $user->id) {
            return $this->sendError('Unauthorized to cancel this leave application', [], 403);
        }
        
        if ($leave->status != 'pending') {
            return $this->sendError('Cannot cancel leave application that is already ' . $leave->status, [], 422);
        }
        
        $leave->update(['status' => 'cancelled']);
        
        return $this->sendResponse([], 'Leave application cancelled successfully');
    }
    
    /**
     * Get leave types
     */
    public function leaveTypes()
    {
        $leaveTypes = LeaveType::whereIn('applicable_for', ['both', 'employee'])
            ->get()
            ->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'days_allowed' => $type->days_allowed,
                    'description' => $type->description,
                ];
            });
        
        return $this->sendResponse($leaveTypes, 'Leave types retrieved successfully');
    }
    
    /**
     * Get leave balance
     */
    public function balance()
    {
        $balance = $this->calculateLeaveBalance();
        
        return $this->sendResponse($balance, 'Leave balance retrieved successfully');
    }
    
    /**
     * Calculate leave balance
     */
    private function calculateLeaveBalance()
    {
        $user = Auth::user();
        $totalLeaves = 20; // Annual leaves
        $carryForward = 0; // Can be configured
        
        $usedLeaves = LeaveApplication::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
        
        $pendingLeaves = LeaveApplication::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('total_days');
        
        return [
            'total' => $totalLeaves + $carryForward,
            'used' => $usedLeaves,
            'pending' => $pendingLeaves,
            'remaining' => ($totalLeaves + $carryForward) - $usedLeaves,
            'used_percentage' => $totalLeaves > 0 ? round(($usedLeaves / $totalLeaves) * 100, 2) : 0,
        ];
    }
    
    /**
     * Calculate working days excluding weekends
     */
    private function calculateWorkingDays($startDate, $endDate)
    {
        $days = 0;
        $current = clone $startDate;
        
        while ($current <= $endDate) {
            if (!$current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }
        
        return $days;
    }
    
    /**
     * Notify admin about new leave application
     */
    private function notifyAdmin($leave)
    {
        $admins = \App\Models\User::whereIn('user_type', ['super_admin', 'admin'])->get();
        
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Leave Application',
                'message' => "Employee {$leave->user->name} has applied for {$leave->total_days} days leave from {$leave->start_date->format('M d, Y')} to {$leave->end_date->format('M d, Y')}",
                'type' => 'leave',
                'priority' => 'medium',
                'data' => [
                    'leave_id' => $leave->id,
                    'employee_name' => $leave->user->name,
                    'start_date' => $leave->start_date->toDateString(),
                    'end_date' => $leave->end_date->toDateString(),
                ],
            ]);
        }
    }
}