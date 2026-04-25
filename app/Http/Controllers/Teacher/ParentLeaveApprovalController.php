<?php
// app/Http/Controllers/Teacher/ParentLeaveApprovalController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ParentLeaveRequest;
use App\Models\Student;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentLeaveApprovalController extends Controller
{
    /**
     * Display list of leave requests for teacher's students
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
            ->whereIn('student_id', $studentIds)
            ->where('status', ParentLeaveRequest::STATUS_PENDING);
        
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
            ->paginate(20)
            ->appends($request->query());
        
        // Get students for filter
        $students = Student::whereIn('class_id', $classIds)
            ->with('user')
            ->get();
        
        return view('teacher.parent-leave-requests.index', compact('leaveRequests', 'students'));
    }
    
    /**
     * Show leave request details
     */
    public function show(ParentLeaveRequest $leaveRequest)
    {
        $teacher = Auth::user();
        
        // Verify teacher teaches this student's class
        $teachesClass = ClassSubject::where('teacher_id', $teacher->id)
            ->where('class_id', $leaveRequest->student->class_id)
            ->exists();
        
        if (!$teachesClass) {
            abort(403, 'Unauthorized to view this leave request.');
        }
        
        $leaveRequest->load(['student.user', 'parent', 'leaveType']);
        
        return view('teacher.parent-leave-requests.show', compact('leaveRequest'));
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
            return redirect()->back()->with('error', 'Unauthorized to approve this leave request.');
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
        
        // Notify parent
        // Notification::send($leaveRequest->parent, new LeaveApprovedByTeacherNotification($leaveRequest));
        
        return redirect()->route('teacher.parent-leave-requests.index')
            ->with('success', 'Leave request approved by teacher. Waiting for admin approval.');
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
            return redirect()->back()->with('error', 'Unauthorized to reject this leave request.');
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
        
        // Notify parent
        // Notification::send($leaveRequest->parent, new LeaveRejectedNotification($leaveRequest));
        
        return redirect()->route('teacher.parent-leave-requests.index')
            ->with('success', 'Leave request rejected.');
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
        
        return response()->json($stats);
    }
}