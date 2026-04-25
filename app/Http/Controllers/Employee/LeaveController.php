<?php
// app/Http/Controllers/Employee/LeaveController.php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    // List leave applications
    public function index()
    {
        $leaves = LeaveApplication::where('user_id', Auth::id())
                                  ->with('leaveType')
                                  ->latest()
                                  ->paginate(20);
        
        $leaveBalance = $this->calculateLeaveBalance();
        $leaveTypes = LeaveType::all();
        
        return view('employee.leaves.index', compact('leaves', 'leaveBalance', 'leaveTypes'));
    }
    
    // Apply for leave
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string'
        ]);
        
        $days = $this->calculateDays($request->start_date, $request->end_date);
        
        // Check leave balance
        $balance = $this->calculateLeaveBalance();
        $leaveType = LeaveType::find($request->leave_type_id);
        
        if ($balance['remaining'] < $days) {
            return back()->withErrors(['error' => 'Insufficient leave balance.']);
        }
        
        $leave = LeaveApplication::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $days,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);
        
        // Notify admin/HR
        Notification::send(Employee::where('department', 'HR')->get(), new LeaveAppliedNotification($leave));
        
        return redirect()->route('employee.leaves.index')
                        ->with('success', 'Leave application submitted successfully.');
    }
    
    // Cancel leave application
    public function cancel(LeaveApplication $leave)
    {
        if ($leave->user_id != Auth::id()) {
            abort(403);
        }
        
        if ($leave->status == 'pending') {
            $leave->update(['status' => 'cancelled']);
            return back()->with('success', 'Leave application cancelled.');
        }
        
        return back()->with('error', 'Cannot cancel this leave application.');
    }
    
    private function calculateLeaveBalance()
    {
        $totalLeaves = 20; // Configure based on employment type
        $usedLeaves = LeaveApplication::where('user_id', Auth::id())
                                      ->where('status', 'approved')
                                      ->whereYear('start_date', now()->year)
                                      ->sum('total_days');
        
        return [
            'total' => $totalLeaves,
            'used' => $usedLeaves,
            'remaining' => $totalLeaves - $usedLeaves
        ];
    }
    
    private function calculateDays($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        return $start->diffInDays($end) + 1;
    }
}