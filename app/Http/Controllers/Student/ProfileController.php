<?php
// app/Http/Controllers/Student/ProfileController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display student profile
     */
    public function show()
    {
        $user = Auth::user();
        $student = $user->student;
        $profile = $user->profile;
        
        $user->load(['profile', 'student.class', 'student.section']);
        
        // Get additional student data
        $parents = $student->parents()->with('user')->get();
        
        // Get attendance summary
        $attendanceSummary = [
            'total_days' => $student->attendances()->count(),
            'present' => $student->attendances()->where('status', 'present')->count(),
            'absent' => $student->attendances()->where('status', 'absent')->count(),
            'percentage' => $student->attendance_percentage,
        ];
        
        // Get performance summary
        $examResults = $student->examResults()
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();
        
        $performanceSummary = [
            'total_exams' => $examResults->groupBy('examSchedule.exam_id')->count(),
            'average_percentage' => $examResults->avg('percentage'),
            'best_subject' => $this->getBestSubject($examResults),
        ];
        
        return view('student.profile.show', compact('user', 'student', 'profile', 'parents', 'attendanceSummary', 'performanceSummary'));
    }
    
    /**
     * Update student profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $profile = $user->profile;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'blood_group' => 'nullable|string|max:10',
            'emergency_contact' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        try {
            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);
            
            // Update profile photo
            if ($request->hasFile('profile_photo')) {
                if ($user->profile_photo) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                $path = $request->file('profile_photo')->store('profiles', 'public');
                $user->profile_photo = $path;
                $user->save();
            }
            
            // Update or create profile
            if ($profile) {
                $profile->update([
                    'date_of_birth' => $request->date_of_birth,
                    'blood_group' => $request->blood_group,
                    'emergency_contact' => $request->emergency_contact,
                    'emergency_contact_name' => $request->emergency_contact_name,
                    'medical_conditions' => $request->medical_conditions,
                ]);
            } else {
                UserProfile::create([
                    'user_id' => $user->id,
                    'date_of_birth' => $request->date_of_birth,
                    'blood_group' => $request->blood_group,
                    'emergency_contact' => $request->emergency_contact,
                    'emergency_contact_name' => $request->emergency_contact_name,
                    'medical_conditions' => $request->medical_conditions,
                ]);
            }
            
            return redirect()->route('student.profile')
                ->with('success', 'Profile updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update profile: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }
        
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        return redirect()->route('student.profile')
            ->with('success', 'Password changed successfully.');
    }
    
    /**
     * Get best subject based on exam results
     */
    private function getBestSubject($examResults)
    {
        $subjectPerformance = [];
        
        foreach ($examResults->groupBy('examSchedule.subject_id') as $subjectId => $subjectResults) {
            $subject = $subjectResults->first()->examSchedule->subject;
            $subjectPerformance[] = [
                'name' => $subject->name,
                'average' => $subjectResults->avg('percentage'),
            ];
        }
        
        if (empty($subjectPerformance)) {
            return null;
        }
        
        $best = collect($subjectPerformance)->sortByDesc('average')->first();
        
        return $best;
    }
}