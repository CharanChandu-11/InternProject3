<?php
// app/Http/Controllers/Api/Employee/TaskController.php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends BaseController
{
    /**
     * Get tasks assigned to employee
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Task::where('assigned_to', $user->id)
            ->with(['assignedBy', 'comments.user']);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Filter by due date range
        if ($request->has('from_date')) {
            $query->whereDate('due_date', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('due_date', '<=', $request->to_date);
        }
        
        $tasks = $query->orderBy('due_date')
            ->orderBy('priority', 'desc')
            ->paginate($request->per_page ?? 20);
        
        // Statistics
        $stats = [
            'total' => Task::where('assigned_to', $user->id)->count(),
            'pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
            'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'completed' => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
            'overdue' => Task::where('assigned_to', $user->id)
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
            'high_priority' => Task::where('assigned_to', $user->id)->where('priority', 'high')->count(),
        ];
        
        return $this->sendResponse([
            'tasks' => TaskResource::collection($tasks),
            'stats' => $stats,
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ], 'Tasks retrieved successfully');
    }
    
    /**
     * Get specific task
     */
    public function show(Task $task)
    {
        $user = Auth::user();
        
        if ($task->assigned_to != $user->id) {
            return $this->sendError('Unauthorized to view this task', [], 403);
        }
        
        $task->load(['assignedBy', 'comments.user']);
        
        return $this->sendResponse(
            new TaskResource($task),
            'Task retrieved successfully'
        );
    }
    
    /**
     * Update task status
     */
    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        
        if ($task->assigned_to != $user->id) {
            return $this->sendError('Unauthorized to update this task', [], 403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'remarks' => 'nullable|string|max:500',
        ]);
        
        $task->update([
            'status' => $request->status,
            'completed_at' => $request->status == 'completed' ? now() : null,
            'remarks' => $request->remarks,
        ]);
        
        // Add comment if status changed to completed
        if ($request->status == 'completed') {
            $task->comments()->create([
                'user_id' => $user->id,
                'comment' => $request->remarks ?? 'Task completed successfully.',
            ]);
        }
        
        // Notify task creator
        if ($task->assigned_by) {
            \App\Models\Notification::create([
                'user_id' => $task->assigned_by,
                'title' => 'Task Status Updated',
                'message' => "Task '{$task->title}' has been marked as " . ucfirst(str_replace('_', ' ', $request->status)) . " by {$user->name}",
                'type' => 'task',
                'priority' => 'medium',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'status' => $request->status,
                    'updated_by' => $user->name,
                ],
            ]);
        }
        
        return $this->sendResponse(
            new TaskResource($task->load(['assignedBy'])),
            'Task status updated successfully'
        );
    }
    
    /**
     * Add comment to task
     */
    public function addComment(Request $request, Task $task)
    {
        $user = Auth::user();
        
        if ($task->assigned_to != $user->id && $task->assigned_by != $user->id) {
            return $this->sendError('Unauthorized to comment on this task', [], 403);
        }
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);
        
        // Notify other party
        $notifyUserId = $task->assigned_to == $user->id ? $task->assigned_by : $task->assigned_to;
        
        if ($notifyUserId) {
            \App\Models\Notification::create([
                'user_id' => $notifyUserId,
                'title' => 'New Comment on Task',
                'message' => "{$user->name} commented on task '{$task->title}'",
                'type' => 'task',
                'priority' => 'low',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'comment_id' => $comment->id,
                    'commented_by' => $user->name,
                ],
            ]);
        }
        
        return $this->sendResponse([
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->profile_photo_url,
                ],
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ], 'Comment added successfully');
    }
    
    /**
     * Get task comments
     */
    public function comments(Task $task)
    {
        $user = Auth::user();
        
        if ($task->assigned_to != $user->id && $task->assigned_by != $user->id) {
            return $this->sendError('Unauthorized to view comments', [], 403);
        }
        
        $comments = $task->comments()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'profile_photo' => $comment->user->profile_photo_url,
                    ],
                    'created_at' => $comment->created_at->diffForHumans(),
                ];
            });
        
        return $this->sendResponse($comments, 'Task comments retrieved successfully');
    }
    
    /**
     * Get task statistics
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'overall' => [
                'total' => Task::where('assigned_to', $user->id)->count(),
                'completed' => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
                'pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
                'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
                'completion_rate' => $this->calculateCompletionRate($user),
            ],
            'by_priority' => [
                'high' => Task::where('assigned_to', $user->id)->where('priority', 'high')->count(),
                'medium' => Task::where('assigned_to', $user->id)->where('priority', 'medium')->count(),
                'low' => Task::where('assigned_to', $user->id)->where('priority', 'low')->count(),
            ],
            'by_status' => [
                'pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
                'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
                'completed' => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
                'overdue' => Task::where('assigned_to', $user->id)
                    ->where('status', '!=', 'completed')
                    ->where('due_date', '<', now())
                    ->count(),
            ],
            'recent_completed' => Task::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->where('completed_at', '>=', now()->subDays(7))
                ->count(),
        ];
        
        return $this->sendResponse($stats, 'Task statistics retrieved successfully');
    }
    
    private function calculateCompletionRate($user)
    {
        $total = Task::where('assigned_to', $user->id)->count();
        $completed = Task::where('assigned_to', $user->id)->where('status', 'completed')->count();
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }
}