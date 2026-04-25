<?php
// app/Http/Controllers/Admin/ParentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParentModel;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    /**
     * Display a listing of parents.
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'parent')
            ->with(['profile', 'parent', 'parent.children']);

        // Search filter
        if ($request->has('search') && $request->search) {
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

        // Parent type filter
        if ($request->has('parent_type') && $request->parent_type) {
            $query->whereHas('parent', function($q) use ($request) {
                $q->where('parent_type', $request->parent_type);
            });
        }

        $parents = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $parentTypes = ['father', 'mother', 'guardian'];

        return view('admin.parents.index', compact('parents', 'parentTypes'));
    }

    /**
     * Show the form for creating a new parent.
     */
    public function create()
    {
        $students = Student::with(['user', 'class', 'section'])->get();
        $parentTypes = ['father', 'mother', 'guardian'];
        
        return view('admin.parents.create', compact('students', 'parentTypes'));
    }

    /**
     * Store a newly created parent in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username|alpha_dash',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'parent_type' => 'required|in:father,mother,guardian',
            'occupation' => 'nullable|string|max:255',
            'office_address' => 'nullable|string',
            'office_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'profile_photo' => 'nullable|image|max:2048',
            'child_ids' => 'nullable|array',
            'child_ids.*' => 'exists:students,id',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'user_type' => 'parent',
            'is_active' => true,
        ]);

        $user->assignRole('parent');

        // Handle profile photo
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->update(['profile_photo' => $path]);
        }

        // Create profile
        $user->profile()->create([
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        // Create parent record
        $parent = ParentModel::create([
            'user_id' => $user->id,
            'parent_type' => $request->parent_type,
            'occupation' => $request->occupation,
            'office_address' => $request->office_address,
            'office_phone' => $request->office_phone,
        ]);

        // Attach children
        if ($request->has('child_ids')) {
            foreach ($request->child_ids as $childId) {
                \DB::table('student_parents')->insert([
                    'student_id' => $childId,
                    'parent_id' => $user->id,
                    'relationship' => $request->parent_type,
                    'is_primary_contact' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent created successfully.');
    }

    /**
     * Display the specified parent.
     */
    public function show(User $parent)
    {
        if ($parent->user_type !== 'parent') {
            return redirect()->route('admin.parents.index')->with('error', 'Invalid parent.');
        }

        $parent->load(['profile', 'parent', 'parent.children.user', 'parent.children.class', 'parent.children.section']);
        
        return view('admin.parents.show', compact('parent'));
    }

    /**
     * Show the form for editing the specified parent.
     */
    public function edit(User $parent)
    {
        if ($parent->user_type !== 'parent') {
            return redirect()->route('admin.parents.index')->with('error', 'Invalid parent.');
        }

        $parent->load(['profile', 'parent', 'parent.children']);
        $students = Student::with(['user', 'class', 'section'])->get();
        $parentTypes = ['father', 'mother', 'guardian'];
        $selectedChildren = $parent->parent->children->pluck('id')->toArray();

        return view('admin.parents.edit', compact('parent', 'students', 'parentTypes', 'selectedChildren'));
    }

    /**
     * Update the specified parent in storage.
     */
    public function update(Request $request, User $parent)
    {
        if ($parent->user_type !== 'parent') {
            return redirect()->route('admin.parents.index')->with('error', 'Invalid parent.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $parent->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'parent_type' => 'required|in:father,mother,guardian',
            'occupation' => 'nullable|string|max:255',
            'office_address' => 'nullable|string',
            'office_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'profile_photo' => 'nullable|image|max:2048',
            'child_ids' => 'nullable|array',
            'child_ids.*' => 'exists:students,id',
        ]);

        // Update user
        $parent->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $parent->update(['password' => Hash::make($request->password)]);
        }

        // Handle profile photo
        if ($request->hasFile('profile_photo')) {
            if ($parent->profile_photo) {
                \Storage::disk('public')->delete($parent->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $parent->update(['profile_photo' => $path]);
        }

        // Update profile
        if ($parent->profile) {
            $parent->profile->update([
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
            ]);
        }

        // Update parent record
        if ($parent->parent) {
            $parent->parent->update([
                'parent_type' => $request->parent_type,
                'occupation' => $request->occupation,
                'office_address' => $request->office_address,
                'office_phone' => $request->office_phone,
            ]);
        }

        // Update children relationships
        \DB::table('student_parents')
            ->where('parent_id', $parent->id)
            ->delete();

        if ($request->has('child_ids')) {
            foreach ($request->child_ids as $childId) {
                \DB::table('student_parents')->insert([
                    'student_id' => $childId,
                    'parent_id' => $parent->id,
                    'relationship' => $request->parent_type,
                    'is_primary_contact' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent updated successfully.');
    }

    /**
     * Remove the specified parent from storage.
     */
    public function destroy(User $parent)
    {
        if ($parent->user_type !== 'parent') {
            return redirect()->route('admin.parents.index')->with('error', 'Invalid parent.');
        }

        // Detach children
        \DB::table('student_parents')
            ->where('parent_id', $parent->id)
            ->delete();

        $parent->delete();

        return redirect()->route('admin.parents.index')
            ->with('success', 'Parent deleted successfully.');
    }

    /**
     * Toggle parent status (activate/deactivate)
     */
    public function toggleStatus(User $parent)
    {
        if ($parent->user_type !== 'parent') {
            return redirect()->route('admin.parents.index')->with('error', 'Invalid parent.');
        }

        $parent->update(['is_active' => !$parent->is_active]);
        
        $status = $parent->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Parent {$status} successfully.");
    }
}