<?php
// app/Http/Controllers/Parent/CommunicationController.php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use App\Models\ClassSubject;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationController extends Controller
{
    public function teachers(Request $request)
    {
        $parent = Auth::user()->parent;
        $children = $parent->children;
        
        $teacherIds = [];
        foreach ($children as $child) {
            $classTeachers = ClassSubject::where('class_id', $child->class_id)
                ->pluck('teacher_id')
                ->toArray();
            $teacherIds = array_merge($teacherIds, $classTeachers);
        }
        
        $teacherIds = array_unique($teacherIds);
        
        $teachers = User::whereIn('id', $teacherIds)
            ->where('user_type', 'teacher')
            ->with(['profile', 'employee'])
            ->get()
            ->map(function($teacher) use ($parent) {
                $lastMessage = Message::where(function($q) use ($parent, $teacher) {
                        $q->where('sender_id', $parent->user_id)
                          ->where('receiver_id', $teacher->id);
                    })->orWhere(function($q) use ($parent, $teacher) {
                        $q->where('sender_id', $teacher->id)
                          ->where('receiver_id', $parent->user_id);
                    })->latest()->first();
                
                $unreadCount = Message::where('sender_id', $teacher->id)
                    ->where('receiver_id', $parent->user_id)
                    ->where('is_read', false)
                    ->count();
                
                return [
                    'teacher' => $teacher,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                ];
            });
        
        return view('parent.communication.teachers', compact('teachers'));
    }
    
    public function conversations()
    {
        $parent = Auth::user()->parent;
        
        $conversations = Message::where('sender_id', $parent->user_id)
            ->orWhere('receiver_id', $parent->user_id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($message) use ($parent) {
                return $message->sender_id == $parent->user_id 
                    ? $message->receiver_id 
                    : $message->sender_id;
            });
        
        $formattedConversations = [];
        foreach ($conversations as $userId => $messages) {
            $otherUser = User::find($userId);
            if ($otherUser && $otherUser->user_type == 'teacher') {
                $lastMessage = $messages->first();
                $unreadCount = Message::where('sender_id', $userId)
                    ->where('receiver_id', $parent->user_id)
                    ->where('is_read', false)
                    ->count();
                
                $formattedConversations[] = [
                    'teacher' => $otherUser,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                ];
            }
        }
        
        return view('parent.communication.conversations', compact('formattedConversations'));
    }
    
    public function conversation(User $teacher)
    {
        $parent = Auth::user()->parent;
        
        if ($teacher->user_type != 'teacher') {
            abort(404);
        }
        
        $messages = Message::where(function($q) use ($parent, $teacher) {
                $q->where('sender_id', $parent->user_id)
                  ->where('receiver_id', $teacher->id);
            })->orWhere(function($q) use ($parent, $teacher) {
                $q->where('sender_id', $teacher->id)
                  ->where('receiver_id', $parent->user_id);
            })->orderBy('created_at', 'asc')
            ->get();
        
        // Mark messages as read
        Message::where('sender_id', $teacher->id)
            ->where('receiver_id', $parent->user_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        
        return view('parent.communication.conversation', compact('teacher', 'messages'));
    }
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);
        
        $parent = Auth::user()->parent;
        $receiver = User::find($request->receiver_id);
        
        if ($receiver->user_type != 'teacher') {
            return redirect()->back()->with('error', 'Can only send messages to teachers.');
        }
        
        Message::create([
            'sender_id' => $parent->user_id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false,
        ]);
        
        return redirect()->back()->with('success', 'Message sent successfully.');
    }
    
    public function markAsRead(Message $message)
    {
        $parent = Auth::user()->parent;
        
        if ($message->receiver_id != $parent->user_id) {
            abort(403);
        }
        
        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Message marked as read.');
    }
    
    public function notifications()
    {
        $parent = Auth::user()->parent;
        
        $notifications = Notification::orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('parent.communication.notifications', compact('notifications'));
    }
    
    public function markNotificationRead(Notification $notification)
    {
        $parent = Auth::user()->parent;
        
        if ($notification->user_id && $notification->user_id != $parent->user_id) {
            abort(403);
        }
        
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Notification marked as read.');
    }
}