<?php
// app/Http/Controllers/Admin/TeacherController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Classes;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'teacher')
            ->with(['profile', 'employee', 'teachingSubjects']);

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name, email, employee_id
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('employee_id', 'like', "%{$search}%")
                         ->orWhere('designation', 'like', "%{$search}%");
                  });
            });
        }

        $teachers = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get departments for filter
        $departments = Employee::whereHas('user', function($q) {
            $q->where('user_type', 'teacher');
        })->distinct()->pluck('department');

        return view('admin.teachers.index', compact('teachers', 'departments'));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create()
    {
        $departments = [
            'Science', 'Mathematics', 'English', 'Social Studies', 
            'Computer Science', 'Physical Education', 'Arts', 'Commerce'
        ];
        
        $designations = [
            'Head of Department', 'Senior Teacher', 'Teacher', 'Assistant Teacher', 'Trainee Teacher'
        ];
        
        $employmentTypes = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'probation' => 'Probation'
        ];
        
        return view('admin.teachers.create', compact('departments', 'designations', 'employmentTypes'));
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|min:8|confirmed',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'qualification' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'employment_type' => 'nullable|in:full_time,part_time,contract,probation',
            'joining_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Generate username
            $username = $this->generateUsername($request->name);

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $username,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'user_type' => 'teacher',
                'is_active' => true,
            ]);

            // Assign role
            $user->assignRole('teacher');

            // Create profile
            if ($request->has('date_of_birth') || $request->has('gender') || $request->has('qualification')) {
                $user->profile()->create([
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                    'emergency_contact' => $request->emergency_contact,
                ]);
            }

            // Generate employee ID
            $employeeId = $this->generateEmployeeId();

            // Create employee record
            Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'employment_type' => $request->employment_type,
                'joining_date' => $request->joining_date ?? now(),
                'department' => $request->department,
                'designation' => $request->designation,
                'salary' => $request->salary,
                'qualification' => $request->qualification,
                'experience_years' => $request->experience_years,
            ]);

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profiles', 'public');
                $user->update(['profile_photo' => $path]);
            }

            DB::commit();

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Teacher created successfully. Employee ID: ' . $employeeId);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create teacher: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(User $teacher)
    {
        if ($teacher->user_type !== 'teacher') {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'User is not a teacher.');
        }

        $teacher->load(['profile', 'employee', 'teachingSubjects.class']);

        // Get classes taught
        $classesTaught = ClassSubject::where('teacher_id', $teacher->id)
            ->with(['class', 'subject', 'class.sections'])
            ->get()
            ->groupBy('class_id');

        // Get recent attendance
        $recentAttendance = $teacher->attendances()
            ->where('attendable_type', 'App\Models\Employee')
            ->where('attendable_id', $teacher->employee->id)
            ->latest()
            ->take(10)
            ->get();

        // Get leave applications
        $leaveApplications = $teacher->leaves()
            ->with('leaveType')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.teachers.show', compact('teacher', 'classesTaught', 'recentAttendance', 'leaveApplications'));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(User $teacher)
    {
        if ($teacher->user_type !== 'teacher') {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'User is not a teacher.');
        }

        $teacher->load(['profile', 'employee']);
        
        $departments = [
            'Science', 'Mathematics', 'English', 'Social Studies', 
            'Computer Science', 'Physical Education', 'Arts', 'Commerce'
        ];
        
        $designations = [
            'Head of Department', 'Senior Teacher', 'Teacher', 'Assistant Teacher', 'Trainee Teacher'
        ];
        
        $employmentTypes = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'probation' => 'Probation'
        ];

        return view('admin.teachers.edit', compact('teacher', 'departments', 'designations', 'employmentTypes'));
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, User $teacher)
    {
        if ($teacher->user_type !== 'teacher') {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'User is not a teacher.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'password' => 'nullable|min:8|confirmed',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'qualification' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'employment_type' => 'nullable|in:full_time,part_time,contract,probation',
            'joining_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'emergency_contact' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Update user
            $teacher->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => $request->has('is_active') ? $request->is_active : $teacher->is_active,
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $teacher->update(['password' => Hash::make($request->password)]);
            }

            // Update profile
            if ($teacher->profile) {
                $teacher->profile->update([
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                    'emergency_contact' => $request->emergency_contact,
                ]);
            }

            // Update employee record
            if ($teacher->employee) {
                $teacher->employee->update([
                    'employment_type' => $request->employment_type,
                    'joining_date' => $request->joining_date,
                    'department' => $request->department,
                    'designation' => $request->designation,
                    'salary' => $request->salary,
                    'qualification' => $request->qualification,
                    'experience_years' => $request->experience_years,
                ]);
            }

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                if ($teacher->profile_photo) {
                    \Storage::disk('public')->delete($teacher->profile_photo);
                }
                $path = $request->file('profile_photo')->store('profiles', 'public');
                $teacher->update(['profile_photo' => $path]);
            }

            DB::commit();

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Teacher updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update teacher: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy(User $teacher)
    {
        if ($teacher->user_type !== 'teacher') {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'User is not a teacher.');
        }

        $teacherName = $teacher->name;
        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher '{$teacherName}' deleted successfully.");
    }

    /**
     * Generate a unique username.
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

    /**
     * Generate a unique employee ID.
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
        
        return 'TCH' . $year . $newNumber;
    }

    public function getTeachersData(Request $request)
    {
        $query = User::where('user_type', 'teacher')
            ->with(['profile', 'employee', 'teachingSubjects']);

        // Apply filters
        if ($request->has('department') && $request->department) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('employee', function($eq) use ($search) {
                    $eq->where('employee_id', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%");
                });
            });
        }

        // Apply sorting
        $columns = ['employee_id', 'name', 'email', 'department', 'designation', 'is_active'];
        $orderColumn = $columns[$request->order[0]['column']] ?? 'created_at';
        $orderDirection = $request->order[0]['dir'] ?? 'desc';
        
        $query->orderBy($orderColumn, $orderDirection);

        // Apply pagination
        $totalRecords = $query->count();
        $teachers = $query->skip($request->start)
            ->take($request->length)
            ->get();

        // Format data for DataTables
        $data = [];
        foreach ($teachers as $teacher) {
            $data[] = [
                'employee_id' => $teacher->employee?->employee_id ?? 'N/A',
                'photo' => '<img src="' . $teacher->profile_photo_url . '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">',
                'name' => $teacher->name,
                'email' => $teacher->email,
                'department' => $teacher->employee?->department ?? '-',
                'designation' => $teacher->employee?->designation ?? '-',
                'status' => '<span class="badge bg-' . ($teacher->is_active ? 'success' : 'danger') . '">' . ($teacher->is_active ? 'Active' : 'Inactive') . '</span>',
                'actions' => '
                    <a href="' . route('admin.teachers.show', $teacher) . '" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . route('admin.teachers.edit', $teacher) . '" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="' . route('admin.teachers.destroy', $teacher) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this teacher?\')">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                '
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => User::where('user_type', 'teacher')->count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }
}