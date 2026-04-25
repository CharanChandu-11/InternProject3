<?php
// app/Http/Controllers/Parent/LeaveRequestController.php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ParentLeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display list of leave requests
     */
    public function index(Request $request)
    {
        $parent = Auth::user()->parent;
        
        $query = ParentLeaveRequest::where('parent_id', $parent->user_id)
            ->with(['student.user', 'leaveType']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());
        
        $children = $parent->children()->with('user')->get();
        $statuses = ParentLeaveRequest::getStatuses();
        
        return view('parent.leave-requests.index', compact('leaveRequests', 'children', 'statuses'));
    }
    
    /**
     * Show form to create leave request
     */
    public function create()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children()->with('user')->get();
        $leaveTypes = LeaveType::whereIn('applicable_for', ['both', 'student'])->get();
        
        return view('parent.leave-requests.create', compact('children', 'leaveTypes'));
    }
    
    /**
     * Store leave request
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:1000',
            'remarks' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);
        
        $parent = Auth::user()->parent;
        $student = $parent->children()->find($request->student_id);
        
        if (!$student) {
            return redirect()->back()->with('error', 'Invalid student selected.');
        }
        
        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Handle attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }
        
        $leaveRequest = ParentLeaveRequest::create([
            'parent_id' => $parent->user_id,
            'student_id' => $request->student_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'remarks' => $request->remarks,
            'attachment' => $attachmentPath,
            'status' => ParentLeaveRequest::STATUS_PENDING,
        ]);
        
        // Notify admin/teacher (optional)
        // $this->notifyAdmin($leaveRequest);
        
        return redirect()->route('parent.leave-requests.index')
            ->with('success', 'Leave request submitted successfully. Awaiting approval.');
    }
    
    /**
     * Show leave request details
     */
    public function show(ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            abort(403);
        }
        
        $leaveRequest->load(['student.user', 'leaveType', 'approver']);
        
        return view('parent.leave-requests.show', compact('leaveRequest'));
    }
    
    /**
     * Show form to edit leave request (only if pending)
     */
    public function edit(ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            abort(403);
        }
        
        if ($leaveRequest->status != ParentLeaveRequest::STATUS_PENDING) {
            return redirect()->route('parent.leave-requests.index')
                ->with('error', 'Cannot edit ' . $leaveRequest->status_text . ' request.');
        }
        
        $children = $parent->children()->with('user')->get();
        $leaveTypes = LeaveType::whereIn('applicable_for', ['both', 'student'])->get();
        
        return view('parent.leave-requests.edit', compact('leaveRequest', 'children', 'leaveTypes'));
    }
    
    /**
     * Update leave request
     */
    public function update(Request $request, ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            abort(403);
        }
        
        if ($leaveRequest->status != ParentLeaveRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Cannot edit ' . $leaveRequest->status_text . ' request.');
        }
        
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:1000',
            'remarks' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);
        
        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            if ($leaveRequest->attachment) {
                Storage::disk('public')->delete($leaveRequest->attachment);
            }
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            $leaveRequest->attachment = $attachmentPath;
        }
        
        $leaveRequest->update([
            'student_id' => $request->student_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'remarks' => $request->remarks,
        ]);
        
        return redirect()->route('parent.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request updated successfully.');
    }
    
    /**
     * Cancel leave request
     */
    public function cancel(ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            abort(403);
        }
        
        if ($leaveRequest->status != ParentLeaveRequest::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Cannot cancel ' . $leaveRequest->status_text . ' request.');
        }
        
        $leaveRequest->update([
            'status' => ParentLeaveRequest::STATUS_CANCELLED,
        ]);
        
        return redirect()->route('parent.leave-requests.index')
            ->with('success', 'Leave request cancelled successfully.');
    }
    
    /**
     * Download attachment
     */
    public function downloadAttachment(ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            abort(403);
        }
        
        if (!$leaveRequest->attachment) {
            abort(404, 'Attachment not found.');
        }
        
        $filePath = storage_path('app/public/' . $leaveRequest->attachment);
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }
        
        $fileName = basename($leaveRequest->attachment);
        return response()->download($filePath, 'leave-attachment-' . $leaveRequest->id . '.pdf');
    }
}