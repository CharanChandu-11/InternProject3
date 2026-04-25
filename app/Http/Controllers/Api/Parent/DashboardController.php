<?php
// app/Http/Controllers/Api/Parent/DashboardController.php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    public function index()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children()->with(['user', 'class', 'section'])->get();
        
        $childrenData = [];
        $totalDue = 0;
        $presentToday = 0;
        
        foreach ($children as $child) {
            $todayAttendance = Attendance::where('attendable_type', 'App\Models\Student')
                ->where('attendable_id', $child->id)
                ->whereDate('attendance_date', today())
                ->first();
            
            if ($todayAttendance && $todayAttendance->status == 'present') {
                $presentToday++;
            }
            
            $dueAmount = $child->fees()->whereIn('status', ['pending', 'partial'])->sum('due_amount');
            $totalDue += $dueAmount;
            
            $childrenData[] = [
                'id' => $child->id,
                'name' => $child->user->name,
                'admission_number' => $child->admission_number,
                'class' => $child->class->name,
                'section' => $child->section->name,
                'attendance_percentage' => $child->attendance_percentage,
                'today_attendance' => $todayAttendance ? $todayAttendance->status : 'not_marked',
                'pending_fees' => $dueAmount,
                'pending_fees_formatted' => '₹ ' . number_format($dueAmount, 2),
            ];
        }
        
        $upcomingEvents = Event::where('start_date', '>=', today())
            ->whereIn('audience', ['all', 'parents'])
            ->orderBy('start_date')
            ->take(5)
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_date->toDateString(),
                    'venue' => $event->venue,
                    'days_left' => Carbon::today()->diffInDays($event->start_date, false),
                ];
            });
        
        $recentMessages = Message::where('receiver_id', $parent->user_id)
            ->orWhere('sender_id', $parent->user_id)
            ->with(['sender', 'receiver'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($message) use ($parent) {
                $otherUser = $message->sender_id == $parent->user_id ? $message->receiver : $message->sender;
                return [
                    'id' => $message->id,
                    'with' => $otherUser->name,
                    'message' => substr($message->message, 0, 100),
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->diffForHumans(),
                ];
            });
        
        $quickStats = [
            'total_children' => $children->count(),
            'present_today' => $presentToday,
            'absent_today' => $children->count() - $presentToday,
            'total_due_fees' => $totalDue,
            'unread_messages' => Message::where('receiver_id', $parent->user_id)
                ->where('is_read', false)
                ->count(),
            'unread_notifications' => Notification::where('is_read', false)
                ->count(),
        ];
        
        return $this->sendResponse([
            'children' => $childrenData,
            'upcoming_events' => $upcomingEvents,
            'recent_messages' => $recentMessages,
            'quick_stats' => $quickStats,
        ], 'Dashboard data retrieved');
    }
    
    public function stats()
    {
        $parent = Auth::user()->parent;
        $children = $parent->children;
        
        $monthlyAttendance = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $present = 0;
            $total = 0;
            
            foreach ($children as $child) {
                $attendances = Attendance::where('attendable_type', 'App\Models\Student')
                    ->where('attendable_id', $child->id)
                    ->whereMonth('attendance_date', $month->month)
                    ->whereYear('attendance_date', $month->year)
                    ->get();
                $present += $attendances->where('status', 'present')->count();
                $total += $attendances->count();
            }
            
            $monthlyAttendance[] = [
                'month' => $month->format('M Y'),
                'present' => $present,
                'absent' => $total - $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        }
        
        return $this->sendResponse([
            'monthly_attendance' => $monthlyAttendance,
            'total_children' => $children->count(),
        ], 'Statistics retrieved');
    }
}