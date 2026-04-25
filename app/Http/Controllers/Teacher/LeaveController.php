<?php
// app/Http/Controllers/Teacher/LeaveController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        $query = LeaveApplication::where('user_id', $teacher->id)
            ->with(['leaveType', 'approvedBy']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $leaves = $query->orderBy('created_at', 'desc')->paginate(20);
        $leaveBalance = $this->calculateLeaveBalance();
        
        return view('teacher.leaves.index', compact('leaves', 'leaveBalance'));
    }
    
    public function create()
    {
        $leaveTypes = LeaveType::whereIn('applicable_for', ['both', 'teacher'])->get();
        $leaveBalance = $this->calculateLeaveBalance();
        
        return view('teacher.leaves.create', compact('leaveTypes', 'leaveBalance'));
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
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $balance = $this->calculateLeaveBalance();
        
        if ($balance['remaining'] < $totalDays) {
            return back()->with('error', 'Insufficient leave balance. Available: ' . $balance['remaining'] . ' days');
        }
        
        LeaveApplication::create([
            'user_id' => $teacher->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);
        
        return redirect()->route('teacher.leaves.index')->with('success', 'Leave application submitted successfully.');
    }
    
    public function show(LeaveApplication $leave)
    {
        if ($leave->user_id != Auth::id()) {
            abort(403);
        }
        
        $leave->load(['leaveType', 'approvedBy']);
        
        return view('teacher.leaves.show', compact('leave'));
    }
    
    public function edit(LeaveApplication $leave)
    {
        if ($leave->user_id != Auth::id() || $leave->status != 'pending') {
            abort(403);
        }
        
        $leaveTypes = LeaveType::whereIn('applicable_for', ['both', 'teacher'])->get();
        
        return view('teacher.leaves.edit', compact('leave', 'leaveTypes'));
    }
    
    public function update(Request $request, LeaveApplication $leave)
    {
        if ($leave->user_id != Auth::id() || $leave->status != 'pending') {
            abort(403);
        }
        
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $leave->update([
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
        ]);
        
        return redirect()->route('teacher.leaves.index')->with('success', 'Leave application updated successfully.');
    }
    
    public function destroy(LeaveApplication $leave)
    {
        if ($leave->user_id != Auth::id() || $leave->status != 'pending') {
            abort(403);
        }
        
        $leave->update(['status' => 'cancelled']);
        
        return redirect()->route('teacher.leaves.index')->with('success', 'Leave application cancelled.');
    }
    
    public function balance()
    {
        $balance = $this->calculateLeaveBalance();
        
        return response()->json($balance);
    }
    
    private function calculateLeaveBalance()
    {
        $teacher = Auth::user();
        $totalLeaves = 20; // Annual leaves
        
        $usedLeaves = LeaveApplication::where('user_id', $teacher->id)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
        
        $pendingLeaves = LeaveApplication::where('user_id', $teacher->id)
            ->where('status', 'pending')
            ->sum('total_days');
        
        return [
            'total' => $totalLeaves,
            'used' => $usedLeaves,
            'pending' => $pendingLeaves,
            'remaining' => $totalLeaves - $usedLeaves,
        ];
    }
}