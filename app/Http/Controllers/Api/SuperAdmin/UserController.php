<?php
// app/Http/Controllers/Api/SuperAdmin/UserController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SuperAdmin\StoreUserRequest;
use App\Http\Requests\Api\SuperAdmin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Exports\UsersExport;

class UserController extends BaseController
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('profile');
        
        // Filter by user type
        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }
    
    /**
     * Store a newly created user
     */
    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Handle photo upload
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profiles', 'public');
                $validated['profile_photo'] = $path;
            }
            
            // Create user
            $validated['password'] = Hash::make($validated['password'] ?? 'password123');
            $user = User::create($validated);
            
            // Assign role based on user type
            $user->assignRole($validated['user_type']);
            
            // Create profile
            if ($request->hasAny(['date_of_birth', 'gender', 'blood_group'])) {
                UserProfile::create([
                    'user_id' => $user->id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'blood_group' => $request->blood_group,
                    'emergency_contact' => $request->emergency_contact,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                ]);
            }
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'user',
                'description' => "Created user: {$user->name}"
            ]);
            
            DB::commit();
            
            return $this->sendResponse(
                new UserResource($user->load('profile')),
                'User created successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create user: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['profile', 'student.class', 'student.section', 'employee', 'parent.children']);
        
        return $this->sendResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }
    
    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Handle photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo
                if ($user->profile_photo) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                
                $path = $request->file('profile_photo')->store('profiles', 'public');
                $validated['profile_photo'] = $path;
            }
            
            // Update password if provided
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            }
            
            // Update user
            $user->update($validated);
            
            // Update profile
            if ($user->profile) {
                $user->profile->update($request->only([
                    'date_of_birth', 'gender', 'blood_group', 'emergency_contact',
                    'qualification', 'experience_years'
                ]));
            }
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'user',
                'description' => "Updated user: {$user->name}"
            ]);
            
            DB::commit();
            
            return $this->sendResponse(
                new UserResource($user->fresh('profile')),
                'User updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update user: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $status,
            'module' => 'user',
            'description' => "{$status} user: {$user->name}"
        ]);
        
        return $this->sendResponse(
            ['is_active' => $user->is_active],
            "User {$status} successfully"
        );
    }
    
    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Delete profile photo
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        
        $userName = $user->name;
        $user->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'user',
            'description' => "Deleted user: {$userName}"
        ]);
        
        return $this->sendResponse([], 'User deleted successfully');
    }
    
    /**
     * Bulk import users
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx|max:10240'
        ]);
        
        try {
            Excel::import(new UsersImport, $request->file('file'));
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_import',
                'module' => 'user',
                'description' => "Imported users from file"
            ]);
            
            return $this->sendResponse([], 'Users imported successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to import users: ' . $e->getMessage());
        }
    }
    
    /**
     * Export users
     */
    public function export(Request $request)
    {
        try {
            $export = new UsersExport($request->all());
            return Excel::download($export, 'users_' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            return $this->sendError('Failed to export users: ' . $e->getMessage());
        }
    }
}