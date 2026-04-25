<?php
// app/Http/Controllers/Parent/DashboardController.php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children()->with(['user', 'class', 'section'])->get();
        
        $childrenData = [];
        $totalDue = 0;
        $presentToday = 0;
        
        foreach ($children as $child) {
            // Today's attendance
            $todayAttendance = Attendance::where('attendable_type', Student::class)
                ->where('attendable_id', $child->id)
                ->whereDate('attendance_date', today())
                ->first();
            
            if ($todayAttendance && $todayAttendance->status == 'present') {
                $presentToday++;
            }
            
            // Fee summary
            $dueAmount = $child->fees()->whereIn('status', ['pending', 'partial'])->sum('due_amount');
            $totalDue += $dueAmount;
            
            // Recent result
            $recentResult = $child->examResults()
                ->with(['examSchedule.exam', 'examSchedule.subject'])
                ->latest()
                ->first();
            
            $childrenData[] = [
                'student' => $child,
                'today_attendance' => $todayAttendance,
                'attendance_percentage' => $child->attendance_percentage,
                'pending_fees' => $dueAmount,
                'pending_fees_formatted' => '₹ ' . number_format($dueAmount, 2),
                'latest_result' => $recentResult,
            ];
        }
        
        // Upcoming events
        $upcomingEvents = Event::where('start_date', '>=', today())
            ->whereIn('audience', ['all', 'parents'])
            ->orderBy('start_date')
            ->take(5)
            ->get();
        
        // Recent announcements
        $announcements = Notification::where('type', 'announcement')
            ->latest()
            ->take(5)
            ->get();
        
        // Recent messages
        $recentMessages = Message::where('receiver_id', $parent->user_id)
            ->orWhere('sender_id', $parent->user_id)
            ->with(['sender', 'receiver'])
            ->latest()
            ->take(5)
            ->get();
        
        $quickStats = [
            'total_children' => $children->count(),
            'present_today' => $presentToday,
            'absent_today' => $children->count() - $presentToday,
            'total_due_fees' => $totalDue,
            'total_due_fees_formatted' => '₹ ' . number_format($totalDue, 2),
            'unread_messages' => Message::where('receiver_id', $parent->user_id)
                ->where('is_read', false)
                ->count(),
            'upcoming_events' => $upcomingEvents->count(),
        ];
        
        return view('parent.dashboard', compact('childrenData', 'upcomingEvents', 'announcements', 'recentMessages', 'quickStats'));
    }
}