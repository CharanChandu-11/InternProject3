<?php
// app/Http/Controllers/Api/MessageController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\Message;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\NewMessageSent;

class MessageController extends BaseController
{
    /**
     * Get all messages for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $messages = Message::where(function($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver', 'parent'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        // Get unique conversations
        $conversations = $this->getConversations($user);

        return $this->sendResponse([
            'conversations' => $conversations,
            'recent_messages' => MessageResource::collection($messages),
            'unread_count' => Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ], 'Messages retrieved successfully');
    }

    /**
     * Get conversation with a specific user
     */
    public function conversation($userId, Request $request)
    {
        $user = Auth::user();
        $otherUser = User::findOrFail($userId);
        
        $messages = Message::where(function($q) use ($user, $otherUser) {
                $q->where(function($sq) use ($user, $otherUser) {
                    $sq->where('sender_id', $user->id)
                       ->where('receiver_id', $otherUser->id);
                })->orWhere(function($sq) use ($user, $otherUser) {
                    $sq->where('sender_id', $otherUser->id)
                       ->where('receiver_id', $user->id);
                });
            })
            ->with(['sender', 'receiver', 'replies'])
            ->orderBy('created_at', 'asc')
            ->paginate($request->per_page ?? 50);

        // Mark messages as read
        Message::where('sender_id', $otherUser->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->sendResponse([
            'user' => new UserResource($otherUser),
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
     * Send a new message
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:messages,id',
        ]);

        $user = Auth::user();
        
        // Prevent sending message to self
        if ($user->id == $request->receiver_id) {
            return $this->sendError('Cannot send message to yourself', [], 422);
        }

        DB::beginTransaction();
        
        try {
            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'parent_id' => $request->parent_id,
                'is_read' => false,
            ]);

            // Load relationships
            $message->load(['sender', 'receiver']);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'sent',
                'module' => 'message',
                'description' => "Sent message to user ID: {$request->receiver_id}",
                'ip_address' => $request->ip(),
            ]);

            // Broadcast real-time event
            event(new NewMessageSent($message));

            DB::commit();

            return $this->sendResponse(
                new MessageResource($message),
                'Message sent successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific message
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $message = Message::where(function($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver', 'replies.sender', 'replies.receiver'])
            ->findOrFail($id);

        // Mark message as read if user is receiver
        if ($message->receiver_id == $user->id && !$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return $this->sendResponse(
            new MessageResource($message),
            'Message retrieved successfully'
        );
    }

    /**
     * Delete a message (soft delete)
     */
    public function destroy($id, Request $request)
    {
        $user = Auth::user();
        
        $message = Message::where(function($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->findOrFail($id);

        $message->delete();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'deleted',
            'module' => 'message',
            'description' => "Deleted message ID: {$id}",
            'ip_address' => $request->ip(),
        ]);

        return $this->sendResponse([], 'Message deleted successfully');
    }

    /**
     * Get unread message count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        $count = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->sendResponse([
            'count' => $count,
        ], 'Unread count retrieved successfully');
    }

    /**
     * Mark message as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $message = Message::where('receiver_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return $this->sendResponse(
            new MessageResource($message),
            'Message marked as read'
        );
    }

    /**
     * Get list of conversations
     */
    private function getConversations($user)
    {
        // Get all users that the current user has conversed with
        $conversationUsers = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->get()
            ->map(function($message) use ($user) {
                return $message->sender_id == $user->id 
                    ? $message->receiver_id 
                    : $message->sender_id;
            })
            ->unique()
            ->values();

        $conversations = [];
        foreach ($conversationUsers as $otherUserId) {
            $otherUser = User::find($otherUserId);
            if (!$otherUser) continue;

            $lastMessage = Message::where(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $user->id)
                      ->where('receiver_id', $otherUserId);
                })
                ->orWhere(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $user->id);
                })
                ->latest()
                ->first();

            $unreadCount = Message::where('sender_id', $otherUserId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'user' => new UserResource($otherUser),
                'last_message' => $lastMessage ? new MessageResource($lastMessage) : null,
                'unread_count' => $unreadCount,
                'last_activity' => $lastMessage?->created_at?->diffForHumans(),
            ];
        }

        // Sort by last activity
        usort($conversations, function($a, $b) {
            $aTime = $a['last_message']?->created_at ?? '1970-01-01';
            $bTime = $b['last_message']?->created_at ?? '1970-01-01';
            return strtotime($bTime) - strtotime($aTime);
        });

        return $conversations;
    }
}