<?php
// app/Http/Controllers/Employee/TaskController.php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('assigned_to', Auth::id());
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->latest()->paginate(20);
        
        return view('employee.tasks.index', compact('tasks'));
    }
    
    public function show(Task $task)
    {
        if ($task->assigned_to != Auth::id()) {
            abort(403);
        }
        
        return view('employee.tasks.show', compact('task'));
    }
    
    public function updateStatus(Request $request, Task $task)
    {
        if ($task->assigned_to != Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);
        
        $task->update([
            'status' => $request->status,
            'completed_at' => $request->status == 'completed' ? now() : null
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function addComment(Request $request, Task $task)
    {
        if ($task->assigned_to != Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'comment' => 'required|string'
        ]);
        
        $task->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);
        
        return back()->with('success', 'Comment added.');
    }
}