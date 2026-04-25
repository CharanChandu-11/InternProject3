<?php
// app/Http/Controllers/SuperAdmin/EmployeeController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\UserProfile;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'employee')
            ->with(['profile', 'employee']);
        
        // Filter by department
        if ($request->filled('department')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        // Filter by employment type
        if ($request->filled('employment_type')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('employment_type', $request->employment_type);
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
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('employee_id', 'like', "%{$search}%")
                         ->orWhere('designation', 'like', "%{$search}%");
                  });
            });
        }
        
        $employees = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $departments = Employee::whereNotNull('department')->distinct()->pluck('department');
        $employmentTypes = ['full_time', 'part_time', 'contract', 'probation', 'temporary'];
        
        return view('super-admin.employees.index', compact('employees', 'departments', 'employmentTypes'));
    }
    
    /**
     * Show form for creating new employee
     */
    public function create()
    {
        return view('super-admin.employees.create');
    }
    
    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contract,probation,temporary',
            'joining_date' => 'required|date',
            'salary' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
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
                'user_type' => 'employee',
                'profile_photo' => $photoPath,
                'is_active' => true,
            ]);
            
            $user->assignRole('employee');
            
            // Create profile
            UserProfile::create([
                'user_id' => $user->id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
                'emergency_contact' => $request->emergency_contact,
                'emergency_contact_name' => $request->emergency_contact_name,
            ]);
            
            // Generate employee ID
            $employeeId = $this->generateEmployeeId();
            
            // Create employee record
            Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'employment_type' => $request->employment_type,
                'joining_date' => $request->joining_date,
                'department' => $request->department,
                'designation' => $request->designation,
                'salary' => $request->salary,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'ifsc_code' => $request->ifsc_code,
                'pan_number' => $request->pan_number,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
            ]);
            
            DB::commit();
            
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'employee',
                'description' => "Created employee: {$user->name} (ID: {$employeeId})",
            ]);
            
            return redirect()->route('super-admin.employees.index')
                ->with('success', "Employee created successfully. Employee ID: {$employeeId}");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create employee: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display employee details
     */
    public function show(User $employee)
    {
        if ($employee->user_type != 'employee') {
            return redirect()->route('super-admin.employees.index')->with('error', 'User is not an employee');
        }
        
        $employee->load(['profile', 'employee', 'attendances' => function($q) {
            $q->latest()->take(30);
        }, 'leaveApplications' => function($q) {
            $q->latest()->take(10);
        }, 'salaryPayments' => function($q) {
            $q->latest()->take(6);
        }]);
        
        // Attendance summary
        $attendanceSummary = [
            'total_days' => $employee->attendances()->count(),
            'present' => $employee->attendances()->where('status', 'present')->count(),
            'absent' => $employee->attendances()->where('status', 'absent')->count(),
            'late' => $employee->attendances()->where('status', 'late')->count(),
            'percentage' => $employee->attendances()->count() > 0 
                ? round(($employee->attendances()->where('status', 'present')->count() / $employee->attendances()->count()) * 100, 2) 
                : 0,
        ];
        
        // Leave summary
        $leaveSummary = [
            'total_leaves' => $employee->leaveApplications()->count(),
            'approved' => $employee->leaveApplications()->where('status', 'approved')->count(),
            'pending' => $employee->leaveApplications()->where('status', 'pending')->count(),
            'rejected' => $employee->leaveApplications()->where('status', 'rejected')->count(),
        ];
        
        return view('super-admin.employees.show', compact('employee', 'attendanceSummary', 'leaveSummary'));
    }
    
    /**
     * Show form for editing employee
     */
    public function edit(User $employee)
    {
        if ($employee->user_type != 'employee') {
            return redirect()->route('super-admin.employees.index')->with('error', 'User is not an employee');
        }
        
        $employee->load(['profile', 'employee']);
        
        return view('super-admin.employees.edit', compact('employee'));
    }
    
    /**
     * Update the specified employee
     */
    public function update(Request $request, User $employee)
    {
        if ($employee->user_type != 'employee') {
            return redirect()->route('super-admin.employees.index')->with('error', 'User is not an employee');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'department' => 'required|string',
            'designation' => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contract,probation,temporary',
            'joining_date' => 'required|date',
            'salary' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update user
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            
            // Update profile photo
            if ($request->hasFile('profile_photo')) {
                if ($employee->profile_photo) {
                    \Storage::disk('public')->delete($employee->profile_photo);
                }
                $photoPath = $request->file('profile_photo')->store('profiles', 'public');
                $employee->update(['profile_photo' => $photoPath]);
            }
            
            // Update password if provided
            if ($request->filled('password')) {
                $request->validate(['password' => 'min:8|confirmed']);
                $employee->update(['password' => Hash::make($request->password)]);
            }
            
            // Update profile
            if ($employee->profile) {
                $employee->profile->update([
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                    'emergency_contact' => $request->emergency_contact,
                    'emergency_contact_name' => $request->emergency_contact_name,
                ]);
            }
            
            // Update employee
            if ($employee->employee) {
                $employee->employee->update([
                    'employment_type' => $request->employment_type,
                    'joining_date' => $request->joining_date,
                    'department' => $request->department,
                    'designation' => $request->designation,
                    'salary' => $request->salary,
                    'bank_name' => $request->bank_name,
                    'bank_account' => $request->bank_account,
                    'ifsc_code' => $request->ifsc_code,
                    'pan_number' => $request->pan_number,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                ]);
            }
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'employee',
                'description' => "Updated employee: {$employee->name}",
            ]);
            
            return redirect()->route('super-admin.employees.index')
                ->with('success', 'Employee updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete employee
     */
    public function destroy(User $employee)
    {
        if ($employee->user_type != 'employee') {
            return redirect()->route('super-admin.employees.index')->with('error', 'User is not an employee');
        }
        
        $employeeName = $employee->name;
        
        // Delete profile photo
        if ($employee->profile_photo) {
            \Storage::disk('public')->delete($employee->profile_photo);
        }
        
        $employee->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'employee',
            'description' => "Deleted employee: {$employeeName}",
        ]);
        
        return redirect()->route('super-admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
    
    /**
     * Toggle employee status
     */
    public function toggleStatus(User $employee)
    {
        if ($employee->user_type != 'employee') {
            return redirect()->back()->with('error', 'User is not an employee');
        }
        
        $employee->update(['is_active' => !$employee->is_active]);
        
        $status = $employee->is_active ? 'activated' : 'deactivated';
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $status,
            'module' => 'employee',
            'description' => "{$status} employee: {$employee->name}",
        ]);
        
        return redirect()->back()->with('success', "Employee {$status} successfully.");
    }
    
    /**
     * Show import form
     */
    public function importForm()
    {
        return view('super-admin.employees.import');
    }
    
    /**
     * Import employees from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:5120',
        ]);
        
        try {
            Excel::import(new EmployeesImport, $request->file('file'));
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'imported',
                'module' => 'employee',
                'description' => "Imported employees from file",
            ]);
            
            return redirect()->route('super-admin.employees.index')
                ->with('success', 'Employees imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing employees: ' . $e->getMessage());
        }
    }
    
    /**
     * Export employees to Excel
     */
    public function export(Request $request)
    {
        return Excel::download(new EmployeesExport($request->all()), 'employees_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Helper: Generate employee ID
     */
    private function generateEmployeeId()
    {
        $year = now()->format('Y');
        $lastEmployee = Employee::whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastEmployee) {
            $lastNumber = intval(substr($lastEmployee->employee_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return 'EMP' . $year . $newNumber;
    }
}