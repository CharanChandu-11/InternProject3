<?php
// app/Http/Controllers/Api/Teacher/ParentLeaveApprovalController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\ParentLeaveRequest;
use App\Models\Student;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentLeaveApprovalController extends BaseController
{
    /**
     * Get leave requests for teacher's students
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        
        // Get class IDs taught by this teacher
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        // Get student IDs in those classes
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        
        $query = ParentLeaveRequest::with(['student.user', 'parent', 'leaveType'])
            ->whereIn('student_id', $studentIds);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', ParentLeaveRequest::STATUS_PENDING);
        }
        
        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('start_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->where('end_date', '<=', $request->to_date);
        }
        
        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        // Get students for filter
        $students = Student::whereIn('class_id', $classIds)
            ->with('user')
            ->get();
        
        // Get statistics
        $stats = [
            'pending' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_PENDING)
                ->count(),
            'approved_by_teacher' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_APPROVED_BY_TEACHER)
                ->count(),
            'approved' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_APPROVED)
                ->count(),
            'rejected' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_REJECTED)
                ->count(),
            'total' => ParentLeaveRequest::whereIn('student_id', $studentIds)->count(),
        ];
        
        return $this->sendResponse([
            'leave_requests' => $leaveRequests->items(),
            'students' => $students,
            'statistics' => $stats,
            'pagination' => [
                'current_page' => $leaveRequests->currentPage(),
                'last_page' => $leaveRequests->lastPage(),
                'per_page' => $leaveRequests->perPage(),
                'total' => $leaveRequests->total(),
            ],
        ], 'Leave requests retrieved successfully');
    }
    
    /**
     * Get single leave request details
     */
    public function show(ParentLeaveRequest $leaveRequest)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this student's class
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $leaveRequest->student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized to view this leave request', [], 403);
        }
        
        $leaveRequest->load(['student.user', 'parent', 'leaveType']);
        
        return $this->sendResponse($leaveRequest, 'Leave request details retrieved');
    }
    
    /**
     * Approve leave request (teacher level)
     */
    public function approve(Request $request, ParentLeaveRequest $leaveRequest)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this student's class
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $leaveRequest->student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized to approve this leave request', [], 403);
        }
        
        $request->validate([
            'teacher_remarks' => 'nullable|string|max:500',
        ]);
        
        $leaveRequest->update([
            'status' => ParentLeaveRequest::STATUS_APPROVED_BY_TEACHER,
            'teacher_id' => $teacher->id,
            'teacher_remarks' => $request->teacher_remarks,
            'teacher_approved_at' => now(),
        ]);
        
        return $this->sendResponse($leaveRequest->fresh(), 'Leave request approved by teacher');
    }
    
    /**
     * Reject leave request
     */
    public function reject(Request $request, ParentLeaveRequest $leaveRequest)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this student's class
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $leaveRequest->student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            return $this->sendError('Unauthorized to reject this leave request', [], 403);
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        $leaveRequest->update([
            'status' => ParentLeaveRequest::STATUS_REJECTED,
            'teacher_id' => $teacher->id,
            'rejection_reason' => $request->rejection_reason,
            'teacher_approved_at' => now(),
        ]);
        
        return $this->sendResponse([], 'Leave request rejected');
    }
    
    /**
     * Get leave statistics for teacher dashboard
     */
    public function statistics()
    {
        $teacher = Auth::user();
        
        // Get class IDs taught by this teacher
        $classIds = ClassSubject::where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique();
        
        // Get student IDs in those classes
        $studentIds = Student::whereIn('class_id', $classIds)->pluck('id');
        
        $stats = [
            'pending' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_PENDING)
                ->count(),
            'approved_by_teacher' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_APPROVED_BY_TEACHER)
                ->count(),
            'approved' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_APPROVED)
                ->count(),
            'rejected' => ParentLeaveRequest::whereIn('student_id', $studentIds)
                ->where('status', ParentLeaveRequest::STATUS_REJECTED)
                ->count(),
            'total' => ParentLeaveRequest::whereIn('student_id', $studentIds)->count(),
        ];
        
        return $this->sendResponse($stats, 'Leave statistics retrieved');
    }
}