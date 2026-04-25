<?php
// app/Http/Controllers/Admin/EmployeeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'user.profile']);

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get unique departments for filter
        $departments = Employee::distinct()->pluck('department')->filter();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'nullable|unique:users',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'profile_photo' => 'nullable|image|max:2048',
            
            'employee_id' => 'required|unique:employees',
            'joining_date' => 'required|date',
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contract,probation',
            'salary' => 'nullable|numeric',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|numeric',
            
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_number' => 'nullable|string',
            
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Generate username if not provided
        if (empty($validated['username'])) {
            $validated['username'] = $this->generateUsername($validated['name']);
        }

        // Handle profile photo
        $photoPath = null;
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('profiles', 'public');
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password'] ?? 'password123'),
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'profile_photo' => $photoPath,
            'user_type' => 'employee',
            'is_active' => true,
        ]);

        $user->assignRole('employee');

        // Create profile
        UserProfile::create([
            'user_id' => $user->id,
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'qualification' => $validated['qualification'],
            'experience_years' => $validated['experience_years'],
        ]);

        // Create employee record
        Employee::create([
            'user_id' => $user->id,
            'employee_id' => $validated['employee_id'],
            'joining_date' => $validated['joining_date'],
            'department' => $validated['department'],
            'designation' => $validated['designation'],
            'employment_type' => $validated['employment_type'],
            'salary' => $validated['salary'],
            'qualification' => $validated['qualification'],
            'experience_years' => $validated['experience_years'],
            'bank_name' => $validated['bank_name'],
            'bank_account' => $validated['bank_account'],
            'ifsc_code' => $validated['ifsc_code'],
            'pan_number' => $validated['pan_number'],
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'user.profile']);
        
        // Calculate attendance percentage
        $attendancePercentage = $employee->attendances()
            ->whereMonth('attendance_date', now()->month)
            ->where('status', 'present')
            ->count() / max(1, $employee->attendances()->whereMonth('attendance_date', now()->month)->count()) * 100;
        
        $leavesTaken = $employee->leaveApplications()
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
        
        return view('admin.employees.show', compact('employee', 'attendancePercentage', 'leavesTaken'));
    }

    public function edit(Employee $employee)
    {
        $employee->load(['user', 'user.profile']);
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'profile_photo' => 'nullable|image|max:2048',
            
            'employee_id' => 'required|unique:employees,employee_id,' . $employee->id,
            'joining_date' => 'required|date',
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contract,probation',
            'salary' => 'nullable|numeric',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|numeric',
            
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_number' => 'nullable|string',
            
            'password' => 'nullable|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        // Update user
        $user = $employee->user;
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update profile photo
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->update(['profile_photo' => $path]);
        }

        // Update profile
        if ($user->profile) {
            $user->profile->update([
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'qualification' => $validated['qualification'],
                'experience_years' => $validated['experience_years'],
            ]);
        }

        // Update employee
        $employee->update([
            'employee_id' => $validated['employee_id'],
            'joining_date' => $validated['joining_date'],
            'department' => $validated['department'],
            'designation' => $validated['designation'],
            'employment_type' => $validated['employment_type'],
            'salary' => $validated['salary'],
            'qualification' => $validated['qualification'],
            'experience_years' => $validated['experience_years'],
            'bank_name' => $validated['bank_name'],
            'bank_account' => $validated['bank_account'],
            'ifsc_code' => $validated['ifsc_code'],
            'pan_number' => $validated['pan_number'],
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $user = $employee->user;
        
        // Delete profile photo
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        
        $employee->delete();
        $user->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function export()
    {
        // Export logic using Excel
        // return Excel::download(new EmployeesExport, 'employees.xlsx');
        return redirect()->back()->with('info', 'Export functionality coming soon.');
    }

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