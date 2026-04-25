<?php
// app/Http/Controllers/SuperAdmin/UserController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Exports\UsersExport;
use Spatie\Permission\Models\Role;
use Illuminate\Pagination\Paginator;

class UserController extends Controller
{
    
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('profile');
        
        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $userTypes = ['super_admin', 'admin', 'employee', 'teacher', 'student', 'parent'];
        
        return view('super-admin.users.index', compact('users', 'userTypes'));
    }
    
    /**
     * Show form for creating new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('super-admin.users.create', compact('roles'));
    }
    
    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'user_type' => 'required|in:super_admin,admin,employee,teacher,student,parent',
            'profile_photo' => 'nullable|image|max:2048',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'emergency_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);
        
        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('profiles', 'public');
        }
        
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'user_type' => $request->user_type,
            'profile_photo' => $photoPath,
            'is_active' => true,
        ]);
        
        // Assign role
        $user->assignRole($request->user_type);
        
        // Create profile
        if ($request->hasAny(['date_of_birth', 'gender', 'blood_group', 'emergency_contact'])) {
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'emergency_contact' => $request->emergency_contact,
            ]);
        }
        
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'module' => 'user',
            'description' => "Created user: {$user->name}",
            'ip_address' => $request->ip(),
        ]);
        
        return redirect()->route('super-admin.users.index')
            ->with('success', 'User created successfully.');
    }
    
    /**
     * Display user details
     */
    public function show(User $user)
    {
        $user->load(['profile', 'student.class', 'student.section', 'employee', 'parent.children']);
        $activities = ActivityLog::where('user_id', $user->id)->latest()->take(10)->get();
        
        return view('super-admin.users.show', compact('user', 'activities'));
    }
    
    /**
     * Show form for editing user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('super-admin.users.edit', compact('user', 'roles'));
    }
    
    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'emergency_contact' => 'nullable|string|max:20',
        ]);
        
        // Handle photo upload
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $photoPath = $request->file('profile_photo')->store('profiles', 'public');
            $user->profile_photo = $photoPath;
        }
        
        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        
        // Update password if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }
        
        // Update profile
        if ($user->profile) {
            $user->profile->update([
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'emergency_contact' => $request->emergency_contact,
            ]);
        }
        
        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'module' => 'user',
            'description' => "Updated user: {$user->name}",
            'ip_address' => $request->ip(),
        ]);
        
        return redirect()->route('super-admin.users.index')
            ->with('success', 'User updated successfully.');
    }
    
    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $status,
            'module' => 'user',
            'description' => "{$status} user: {$user->name}",
        ]);
        
        return redirect()->back()->with('success', "User {$status} successfully.");
    }
    
    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        
        $userName = $user->name;
        $user->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'user',
            'description' => "Deleted user: {$userName}",
        ]);
        
        return redirect()->route('super-admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    /**
     * Show import form
     */
    public function importForm()
    {
        return view('super-admin.users.import');
    }
    
    /**
     * Import users from Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:5120',
        ]);
        
        try {
            Excel::import(new UsersImport, $request->file('file'));
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'imported',
                'module' => 'user',
                'description' => "Imported users from file",
            ]);
            
            return redirect()->route('super-admin.users.index')
                ->with('success', 'Users imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing users: ' . $e->getMessage());
        }
    }
    
    /**
     * Export users to Excel
     */
    public function export(Request $request)
    {
        return Excel::download(new UsersExport($request->all()), 'users_' . date('Y-m-d') . '.xlsx');
    }
}