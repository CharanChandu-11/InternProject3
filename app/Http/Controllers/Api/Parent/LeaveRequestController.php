<?php
// app/Http/Controllers/Api/Parent/LeaveRequestController.php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Api\BaseController;
use App\Models\ParentLeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveRequestController extends BaseController
{
    /**
     * Get all leave requests for parent
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
            ->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse($leaveRequests, 'Leave requests retrieved');
    }
    
    /**
     * Get leave types for students
     */
    public function leaveTypes()
    {
        $leaveTypes = LeaveType::whereIn('applicable_for', ['both', 'student'])->get();
        return $this->sendResponse($leaveTypes, 'Leave types retrieved');
    }
    
    /**
     * Get parent's children
     */
    public function children()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children()->with('user')->get();
        
        return $this->sendResponse($children, 'Children retrieved');
    }
    
    /**
     * Create leave request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:1000',
            'remarks' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', [$validator->errors()], 422);
        }
        
        $parent = Auth::user()->parent;
        $student = $parent->children()->find($request->student_id);
        
        if (!$student) {
            return $this->sendError('Invalid student selected', [], 422);
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
        
        return $this->sendResponse($leaveRequest->load(['student.user', 'leaveType']), 'Leave request submitted', 201);
    }
    
    /**
     * Get single leave request
     */
    public function show(ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        $leaveRequest->load(['student.user', 'leaveType', 'approver']);
        
        return $this->sendResponse($leaveRequest, 'Leave request retrieved');
    }
    
    /**
     * Update leave request (only if pending)
     */
    public function update(Request $request, ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        if ($leaveRequest->status != ParentLeaveRequest::STATUS_PENDING) {
            return $this->sendError('Cannot update ' . $leaveRequest->status_text . ' request', [], 422);
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
        
        return $this->sendResponse($leaveRequest->fresh(), 'Leave request updated');
    }
    
    /**
     * Cancel leave request
     */
    public function cancel(ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        if ($leaveRequest->status != ParentLeaveRequest::STATUS_PENDING) {
            return $this->sendError('Cannot cancel ' . $leaveRequest->status_text . ' request', [], 422);
        }
        
        $leaveRequest->update([
            'status' => ParentLeaveRequest::STATUS_CANCELLED,
        ]);
        
        return $this->sendResponse([], 'Leave request cancelled');
    }
    
    /**
     * Download attachment
     */
    public function download(ParentLeaveRequest $leaveRequest)
    {
        $parent = Auth::user()->parent;
        
        if ($leaveRequest->parent_id != $parent->user_id) {
            return $this->sendError('Unauthorized', [], 403);
        }
        
        if (!$leaveRequest->attachment) {
            return $this->sendError('Attachment not found', [], 404);
        }
        
        $filePath = storage_path('app/public/' . $leaveRequest->attachment);
        if (!file_exists($filePath)) {
            return $this->sendError('File not found', [], 404);
        }
        
        $fileName = 'leave-attachment-' . $leaveRequest->id . '.pdf';
        return response()->download($filePath, $fileName);
    }
}