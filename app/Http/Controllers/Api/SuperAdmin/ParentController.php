<?php
// app/Http/Controllers/Api/SuperAdmin/ParentController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SuperAdmin\StoreParentRequest;
use App\Http\Requests\Api\SuperAdmin\UpdateParentRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\ParentModel;
use App\Models\UserProfile;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ParentController extends BaseController
{
    /**
     * Display a listing of parents
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'parent')
            ->with(['profile', 'parent', 'parent.children.user']);
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('parent', function($pq) use ($search) {
                      $pq->where('occupation', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $parents = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return $this->sendPaginatedResponse(
            UserResource::collection($parents),
            'Parents retrieved successfully'
        );
    }
    
    /**
     * Store a newly created parent
     */
    public function store(StoreParentRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $this->generateUsername($validated['name']),
                'password' => Hash::make($validated['password'] ?? 'password123'),
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'user_type' => 'parent',
                'is_active' => true
            ]);
            
            $user->assignRole('parent');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'emergency_contact' => $validated['emergency_contact'] ?? null
            ]);
            
            // Create parent record
            ParentModel::create([
                'user_id' => $user->id,
                'parent_type' => $validated['parent_type'],
                'occupation' => $validated['occupation'] ?? null,
                'office_address' => $validated['office_address'] ?? null,
                'office_phone' => $validated['office_phone'] ?? null
            ]);
            
            // Attach children if provided
            if ($request->has('child_ids')) {
                $parent = ParentModel::where('user_id', $user->id)->first();
                foreach ($request->child_ids as $childId) {
                    DB::table('student_parents')->insert([
                        'student_id' => $childId,
                        'parent_id' => $user->id,
                        'relationship' => $validated['parent_type'],
                        'is_primary_contact' => $request->is_primary_contact ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'parent',
                'description' => "Created parent: {$user->name}"
            ]);
            
            return $this->sendResponse(
                new UserResource($user->load(['profile', 'parent', 'parent.children'])),
                'Parent created successfully',
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create parent: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified parent
     */
    public function show(User $parent)
    {
        if ($parent->user_type != 'parent') {
            return $this->sendError('User is not a parent', [], 404);
        }
        
        $parent->load(['profile', 'parent', 'parent.children.user', 'parent.children.class', 'parent.children.section']);
        
        return $this->sendResponse(
            new UserResource($parent),
            'Parent retrieved successfully'
        );
    }
    
    /**
     * Update the specified parent
     */
    public function update(UpdateParentRequest $request, User $parent)
    {
        if ($parent->user_type != 'parent') {
            return $this->sendError('User is not a parent', [], 404);
        }
        
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Update user
            $parent->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address']
            ]);
            
            // Update profile
            if ($parent->profile) {
                $parent->profile->update([
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'emergency_contact' => $validated['emergency_contact'] ?? null
                ]);
            }
            
            // Update parent record
            if ($parent->parent) {
                $parent->parent->update([
                    'parent_type' => $validated['parent_type'],
                    'occupation' => $validated['occupation'] ?? null,
                    'office_address' => $validated['office_address'] ?? null,
                    'office_phone' => $validated['office_phone'] ?? null
                ]);
            }
            
            // Update password if provided
            if ($request->filled('password')) {
                $parent->update(['password' => Hash::make($request->password)]);
            }
            
            // Sync children
            if ($request->has('child_ids')) {
                DB::table('student_parents')
                    ->where('parent_id', $parent->id)
                    ->delete();
                
                foreach ($request->child_ids as $childId) {
                    DB::table('student_parents')->insert([
                        'student_id' => $childId,
                        'parent_id' => $parent->id,
                        'relationship' => $validated['parent_type'],
                        'is_primary_contact' => $request->is_primary_contact ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'parent',
                'description' => "Updated parent: {$parent->name}"
            ]);
            
            return $this->sendResponse(
                new UserResource($parent->fresh(['profile', 'parent', 'parent.children'])),
                'Parent updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update parent: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified parent
     */
    public function destroy(User $parent)
    {
        if ($parent->user_type != 'parent') {
            return $this->sendError('User is not a parent', [], 404);
        }
        
        $parentName = $parent->name;
        
        // Detach from children
        DB::table('student_parents')
            ->where('parent_id', $parent->id)
            ->delete();
        
        $parent->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'parent',
            'description' => "Deleted parent: {$parentName}"
        ]);
        
        return $this->sendResponse([], 'Parent deleted successfully');
    }
    
    /**
     * Helper: Generate username
     */
    private function generateUsername($name)
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $username = $base;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }
}