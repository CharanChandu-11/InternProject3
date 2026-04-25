<?php
// app/Http/Controllers/Api/Parent/CommunicationController.php

namespace App\Http\Controllers\Api\Parent;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\UserResource;
use App\Http\Resources\MessageResource;
use App\Models\User;
use App\Models\Message;
use App\Models\ClassSubject;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\NewMessageSent;

class CommunicationController extends BaseController
{
    /**
     * Get all teachers for parent's children
     */
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
                // Get recent message
                $lastMessage = Message::where(function($q) use ($parent, $teacher) {
                        $q->where('sender_id', $parent->user_id)
                          ->where('receiver_id', $teacher->id);
                    })
                    ->orWhere(function($q) use ($parent, $teacher) {
                        $q->where('sender_id', $teacher->id)
                          ->where('receiver_id', $parent->user_id);
                    })
                    ->latest()
                    ->first();
                
                $unreadCount = Message::where('sender_id', $teacher->id)
                    ->where('receiver_id', $parent->user_id)
                    ->where('is_read', false)
                    ->count();
                
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'phone' => $teacher->phone,
                    'profile_photo' => $teacher->profile_photo_url,
                    'designation' => $teacher->employee?->designation,
                    'qualification' => $teacher->profile?->qualification,
                    'subjects' => ClassSubject::where('teacher_id', $teacher->id)
                        ->with('subject')
                        ->get()
                        ->pluck('subject.name')
                        ->unique()
                        ->values(),
                    'classes' => ClassSubject::where('teacher_id', $teacher->id)
                        ->with('class')
                        ->get()
                        ->pluck('class.name')
                        ->unique()
                        ->values(),
                    'last_message' => $lastMessage ? [
                        'message' => $lastMessage->message,
                        'is_from_me' => $lastMessage->sender_id == $parent->user_id,
                        'created_at' => $lastMessage->created_at->diffForHumans(),
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            });
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $teachers = $teachers->filter(function($teacher) use ($search) {
                return stripos($teacher['name'], $search) !== false ||
                       stripos($teacher['email'], $search) !== false ||
                       stripos($teacher['subjects']->implode(','), $search) !== false;
            })->values();
        }
        
        return $this->sendResponse($teachers, 'Teachers retrieved successfully');
    }
    
    /**
     * Send message to a teacher
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
            'student_id' => 'nullable|exists:students,id',
        ]);
        
        $parent = Auth::user()->parent;
        $receiver = User::find($request->receiver_id);
        
        // Verify receiver is a teacher
        if ($receiver->user_type != 'teacher') {
            return $this->sendError('Can only send messages to teachers', [], 422);
        }
        
        // If student_id provided, verify it belongs to parent
        if ($request->has('student_id')) {
            $student = Student::find($request->student_id);
            if (!$parent->children->contains($student)) {
                return $this->sendError('Invalid student selected', [], 422);
            }
        }
        
        DB::beginTransaction();
        
        try {
            $message = Message::create([
                'sender_id' => $parent->user_id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'student_id' => $request->student_id,
                'is_read' => false,
            ]);
            
            // Load relationships
            $message->load(['sender', 'receiver']);
            
            // Broadcast real-time event
            event(new NewMessageSent($message));
            
            DB::commit();
            
            return $this->sendResponse(
                new MessageResource($message),
                'Message sent successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to send message: ' . $e->getMessage(), [], 500);
        }
    }
    
    /**
     * Get all conversations
     */
    public function conversations(Request $request)
    {
        $parent = Auth::user()->parent;
        
        $conversations = Message::where('sender_id', $parent->user_id)
            ->orWhere('receiver_id', $parent->user_id)
            ->with(['sender', 'receiver', 'student'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($message) use ($parent) {
                return $message->sender_id == $parent->user_id 
                    ? $message->receiver_id 
                    : $message->sender_id;
            });
        
        $formattedConversations = [];
        foreach ($conversations as $otherUserId => $messages) {
            $otherUser = User::find($otherUserId);
            $lastMessage = $messages->first();
            $unreadCount = Message::where('sender_id', $otherUserId)
                ->where('receiver_id', $parent->user_id)
                ->where('is_read', false)
                ->count();
            
            $formattedConversations[] = [
                'user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'profile_photo' => $otherUser->profile_photo_url,
                    'designation' => $otherUser->employee?->designation,
                ],
                'last_message' => [
                    'message' => $lastMessage->message,
                    'created_at' => $lastMessage->created_at->diffForHumans(),
                    'is_from_me' => $lastMessage->sender_id == $parent->user_id,
                ],
                'unread_count' => $unreadCount,
                'student' => $lastMessage->student ? [
                    'id' => $lastMessage->student->id,
                    'name' => $lastMessage->student->full_name,
                ] : null,
            ];
        }
        
        return $this->sendResponse($formattedConversations, 'Conversations retrieved successfully');
    }
    
    /**
     * Get conversation with a specific teacher
     */
    public function conversation($teacherId, Request $request)
    {
        $parent = Auth::user()->parent;
        $teacher = User::findOrFail($teacherId);
        
        // Verify teacher exists
        if ($teacher->user_type != 'teacher') {
            return $this->sendError('Invalid teacher', [], 404);
        }
        
        $messages = Message::where(function($q) use ($parent, $teacher) {
                $q->where('sender_id', $parent->user_id)
                  ->where('receiver_id', $teacher->id);
            })
            ->orWhere(function($q) use ($parent, $teacher) {
                $q->where('sender_id', $teacher->id)
                  ->where('receiver_id', $parent->user_id);
            })
            ->with(['sender', 'receiver', 'student'])
            ->orderBy('created_at', 'asc')
            ->paginate($request->per_page ?? 50);
        
        // Mark messages as read
        Message::where('sender_id', $teacher->id)
            ->where('receiver_id', $parent->user_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        
        return $this->sendResponse([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'profile_photo' => $teacher->profile_photo_url,
                'designation' => $teacher->employee?->designation,
                'qualification' => $teacher->profile?->qualification,
            ],
            'messages' => MessageResource::collection($messages),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ], 'Conversation retrieved successfully');
    }
    
    /**
     * Get unread message count
     */
    public function unreadCount()
    {
        $parent = Auth::user()->parent;
        
        $count = Message::where('receiver_id', $parent->user_id)
            ->where('is_read', false)
            ->count();
        
        return $this->sendResponse([
            'unread_count' => $count,
        ], 'Unread count retrieved successfully');
    }
    
    /**
     * Mark message as read
     */
    public function markAsRead($messageId)
    {
        $parent = Auth::user()->parent;
        
        $message = Message::where('receiver_id', $parent->user_id)
            ->where('id', $messageId)
            ->first();
        
        if (!$message) {
            return $this->sendError('Message not found', [], 404);
        }
        
        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
        
        return $this->sendResponse([], 'Message marked as read');
    }
}