<?php
// app/Http/Controllers/SuperAdmin/ParentController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParentModel;
use App\Models\UserProfile;
use App\Models\Student;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParentsImport;

class ParentController extends Controller
{
    /**
     * Display a listing of parents
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'parent')
            ->with(['profile', 'parent', 'parent.children.user', 'parent.children.class']);
        
        // Filter by parent type
        if ($request->filled('parent_type')) {
            $query->whereHas('parent', function($q) use ($request) {
                $q->where('parent_type', $request->parent_type);
            });
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
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('parent', function($pq) use ($search) {
                      $pq->where('occupation', 'like', "%{$search}%");
                  });
            });
        }
        
        $parents = $query->orderBy('created_at', 'desc')->paginate(20);
        $parentTypes = ['father', 'mother', 'guardian', 'step_father', 'step_mother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other'];
        
        return view('super-admin.parents.index', compact('parents', 'parentTypes'));
    }
    
    /**
     * Show form for creating new parent
     */
    public function create()
    {
        $students = Student::with(['user', 'class', 'section'])->get();
        $parentTypes = ['father', 'mother', 'guardian', 'step_father', 'step_mother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other'];
        
        return view('super-admin.parents.create', compact('students', 'parentTypes'));
    }
    
    /**
     * Store a newly created parent
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'parent_type' => 'required|in:father,mother,guardian,step_father,step_mother,grandfather,grandmother,uncle,aunt,other',
            'occupation' => 'nullable|string',
            'office_address' => 'nullable|string',
            'office_phone' => 'nullable|string',
            'annual_income' => 'nullable|numeric',
            'qualification' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'child_ids' => 'nullable|array',
            'child_ids.*' => 'exists:students,id',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('profiles', 'public');
            }
            
            // Create user account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'user_type' => 'parent',
                'profile_photo' => $photoPath,
                'is_active' => true,
            ]);
            
            $user->assignRole('parent');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'qualification' => $request->qualification,
                'emergency_contact' => $request->emergency_contact,
                'alternative_phone' => $request->alternative_phone,
            ]);
            
            // Create parent record
            $parent = ParentModel::create([
                'user_id' => $user->id,
                'parent_type' => $request->parent_type,
                'occupation' => $request->occupation,
                'office_address' => $request->office_address,
                'office_phone' => $request->office_phone,
                'annual_income' => $request->annual_income,
                'qualification' => $request->qualification,
            ]);
            
            // Attach children
            if ($request->has('child_ids')) {
                foreach ($request->child_ids as $childId) {
                    DB::table('student_parents')->insert([
                        'student_id' => $childId,
                        'parent_id' => $user->id,
                        'relationship' => $request->parent_type,
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
                'description' => "Created parent: {$user->name}",
            ]);
            
            return redirect()->route('super-admin.parents.index')
                ->with('success', "Parent created successfully.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create parent: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display parent details
     */
    public function show(User $parent)
    {
        if ($parent->user_type != 'parent') {
            return redirect()->route('super-admin.parents.index')->with('error', 'User is not a parent');
        }
        
        $parent->load(['profile', 'parent', 'parent.children.user', 'parent.children.class', 'parent.children.section']);
        
        // Get children with detailed information
        $children = [];
        foreach ($parent->parent->children as $child) {
            $children[] = [
                'id' => $child->id,
                'name' => $child->user->name,
                'admission_number' => $child->admission_number,
                'class' => $child->class->name,
                'section' => $child->section->name,
                'roll_number' => $child->roll_number,
                'attendance_percentage' => $child->attendance_percentage,
                'fee_due' => $child->fees()->whereIn('status', ['pending', 'partial'])->sum('due_amount'),
                'relationship' => $child->pivot->relationship,
                'is_primary_contact' => $child->pivot->is_primary_contact,
            ];
        }
        
        // Get recent activities
        $activities = ActivityLog::where('user_id', $parent->id)->latest()->take(10)->get();
        
        return view('super-admin.parents.show', compact('parent', 'children', 'activities'));
    }
    
    /**
     * Show form for editing parent
     */
    public function edit(User $parent)
    {
        if ($parent->user_type != 'parent') {
            return redirect()->route('super-admin.parents.index')->with('error', 'User is not a parent');
        }
        
        $parent->load(['profile', 'parent', 'parent.children']);
        $students = Student::with(['user', 'class', 'section'])->get();
        $parentTypes = ['father', 'mother', 'guardian', 'step_father', 'step_mother', 'grandfather', 'grandmother', 'uncle', 'aunt', 'other'];
        $selectedChildren = $parent->parent->children->pluck('id')->toArray();
        
        return view('super-admin.parents.edit', compact('parent', 'students', 'parentTypes', 'selectedChildren'));
    }
    
    /**
     * Update the specified parent
     */
    public function update(Request $request, User $parent)
    {
        if ($parent->user_type != 'parent') {
            return redirect()->route('super-admin.parents.index')->with('error', 'User is not a parent');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $parent->id,
            'phone' => 'required|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'parent_type' => 'required|in:father,mother,guardian,step_father,step_mother,grandfather,grandmother,uncle,aunt,other',
            'occupation' => 'nullable|string',
            'office_address' => 'nullable|string',
            'office_phone' => 'nullable|string',
            'annual_income' => 'nullable|numeric',
            'qualification' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'child_ids' => 'nullable|array',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update user
            $parent->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            
            // Update profile photo
            if ($request->hasFile('profile_photo')) {
                if ($parent->profile_photo) {
                    \Storage::disk('public')->delete($parent->profile_photo);
                }
                $photoPath = $request->file('profile_photo')->store('profiles', 'public');
                $parent->update(['profile_photo' => $photoPath]);
            }
            
            // Update password if provided
            if ($request->filled('password')) {
                $request->validate(['password' => 'min:8|confirmed']);
                $parent->update(['password' => Hash::make($request->password)]);
            }
            
            // Update profile
            if ($parent->profile) {
                $parent->profile->update([
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'qualification' => $request->qualification,
                    'emergency_contact' => $request->emergency_contact,
                    'alternative_phone' => $request->alternative_phone,
                ]);
            }
            
            // Update parent record
            if ($parent->parent) {
                $parent->parent->update([
                    'parent_type' => $request->parent_type,
                    'occupation' => $request->occupation,
                    'office_address' => $request->office_address,
                    'office_phone' => $request->office_phone,
                    'annual_income' => $request->annual_income,
                    'qualification' => $request->qualification,
                ]);
            }
            
            // Sync children
            if ($request->has('child_ids')) {
                // Remove existing relationships
                DB::table('student_parents')->where('parent_id', $parent->id)->delete();
                
                // Add new relationships
                foreach ($request->child_ids as $childId) {
                    DB::table('student_parents')->insert([
                        'student_id' => $childId,
                        'parent_id' => $parent->id,
                        'relationship' => $request->parent_type,
                        'is_primary_contact' => $request->is_primary_contact ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'parent',
                'description' => "Updated parent: {$parent->name}",
            ]);
            
            return redirect()->route('super-admin.parents.index')
                ->with('success', 'Parent updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update parent: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete parent
     */
    public function destroy(User $parent)
    {
        if ($parent->user_type != 'parent') {
            return redirect()->route('super-admin.parents.index')->with('error', 'User is not a parent');
        }
        
        $parentName = $parent->name;
        
        // Delete profile photo
        if ($parent->profile_photo) {
            \Storage::disk('public')->delete($parent->profile_photo);
        }
        
        // Remove child relationships
        DB::table('student_parents')->where('parent_id', $parent->id)->delete();
        
        $parent->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'parent',
            'description' => "Deleted parent: {$parentName}",
        ]);
        
        return redirect()->route('super-admin.parents.index')
            ->with('success', 'Parent deleted successfully.');
    }
    
    /**
     * Toggle parent status
     */
    public function toggleStatus(User $parent)
    {
        if ($parent->user_type != 'parent') {
            return redirect()->back()->with('error', 'User is not a parent');
        }
        
        $parent->update(['is_active' => !$parent->is_active]);
        
        $status = $parent->is_active ? 'activated' : 'deactivated';
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $status,
            'module' => 'parent',
            'description' => "{$status} parent: {$parent->name}",
        ]);
        
        return redirect()->back()->with('success', "Parent {$status} successfully.");
    }
    
    /**
     * Show import form
     */
    public function importForm()
    {
        $students = Student::with(['user', 'class', 'section'])->get();
        return view('super-admin.parents.import', compact('students'));
    }
    
    /**
     * Import parents from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:5120',
        ]);
        
        try {
            Excel::import(new ParentsImport, $request->file('file'));
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'imported',
                'module' => 'parent',
                'description' => "Imported parents from file",
            ]);
            
            return redirect()->route('super-admin.parents.index')
                ->with('success', 'Parents imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing parents: ' . $e->getMessage());
        }
    }
    
    /**
     * Export parents to Excel
     */
    public function export(Request $request)
    {
        return Excel::download(new ParentsExport($request->all()), 'parents_' . date('Y-m-d') . '.xlsx');
    }
}