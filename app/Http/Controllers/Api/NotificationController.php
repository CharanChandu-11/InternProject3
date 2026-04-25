<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends BaseController
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = Notification::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('user_id', null); // Broadcast notifications
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        $unreadCount = Notification::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('user_id', null);
            })
            ->where('is_read', false)
            ->count();

        return $this->sendResponse([
            'notifications' => NotificationResource::collection($notifications),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'unread_count' => $unreadCount,
        ], 'Notifications retrieved successfully');
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('user_id', null);
            })
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->sendError('Notification not found', [], 404);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $this->sendResponse(
            new NotificationResource($notification),
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $updated = Notification::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('user_id', null);
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->sendResponse([
            'marked_count' => $updated,
        ], 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('user_id', null);
            })
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->sendError('Notification not found', [], 404);
        }

        $notification->delete();

        return $this->sendResponse([], 'Notification deleted successfully');
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $user = Auth::user();
        
        $deleted = Notification::where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('user_id', null);
            })
            ->where('is_read', true)
            ->delete();

        return $this->sendResponse([
            'deleted_count' => $deleted,
        ], 'All read notifications deleted');
    }

    /**
     * Get notification settings
     */
    public function settings()
    {
        $user = Auth::user();
        
        // Get or create notification settings for user
        $settings = $user->notificationSettings ?? [
            'email' => true,
            'sms' => false,
            'push' => true,
            'in_app' => true,
        ];

        return $this->sendResponse($settings, 'Notification settings retrieved');
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'email' => 'sometimes|boolean',
            'sms' => 'sometimes|boolean',
            'push' => 'sometimes|boolean',
            'in_app' => 'sometimes|boolean',
        ]);

        $settings = $user->notificationSettings()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['email', 'sms', 'push', 'in_app'])
        );

        return $this->sendResponse($settings, 'Notification settings updated');
    }
}