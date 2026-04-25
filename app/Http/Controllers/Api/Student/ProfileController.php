<?php
// app/Http/Controllers/Api/Student/ProfileController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    /**
     * Get student profile
     */
    public function show()
    {
        $user = Auth::user();
        $user->load(['profile', 'student.class', 'student.section', 'student.parents.user']);

        $student = $user->student;

        // Additional summaries
        $attendanceSummary = [
            'total_days' => $student->attendances()->count(),
            'present' => $student->attendances()->where('status', 'present')->count(),
            'absent' => $student->attendances()->where('status', 'absent')->count(),
            'percentage' => $student->attendance_percentage,
        ];

        $examResults = $student->examResults()
            ->with(['examSchedule.exam', 'examSchedule.subject'])
            ->get();

        $performanceSummary = [
            'total_exams' => $examResults->groupBy('examSchedule.exam_id')->count(),
            'average_percentage' => round($examResults->avg('percentage'), 2),
            'best_subject' => $this->getBestSubject($examResults),
        ];

        $parents = $student->parents->map(fn($parent) => [
            'id' => $parent->id,
            'name' => $parent->name,
            'email' => $parent->email,
            'phone' => $parent->phone,
            'relationship' => $parent->pivot->relationship,
            'occupation' => $parent->parent?->occupation,
        ]);

        return $this->sendResponse([
            'user' => new UserResource($user),
            'student' => [
                'id' => $student->id,
                'admission_number' => $student->admission_number,
                'admission_date' => $student->admission_date->toDateString(),
                'roll_number' => $student->roll_number,
                'class' => $student->class->name,
                'section' => $student->section->name,
                'previous_school' => $student->previous_school,
                'previous_grade' => $student->previous_grade,
            ],
            'parents' => $parents,
            'attendance_summary' => $attendanceSummary,
            'performance_summary' => $performanceSummary,
        ], 'Profile retrieved successfully');
    }

    /**
     * Update student profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $profile = $user->profile;

        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }

        try {
            // Update user
            $user->fill([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ])->save();

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
                $profile->fill([
                    'date_of_birth' => $request->date_of_birth,
                    'blood_group' => $request->blood_group,
                    'emergency_contact' => $request->emergency_contact,
                    'emergency_contact_name' => $request->emergency_contact_name,
                    'medical_conditions' => $request->medical_conditions,
                ])->save();
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

            return $this->sendResponse(new UserResource($user->fresh('profile')), 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update profile: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect', [], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->sendResponse([], 'Password changed successfully');
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
                'average' => round($subjectResults->avg('percentage'), 2),
            ];
        }

        if (empty($subjectPerformance)) {
            return null;
        }

        return collect($subjectPerformance)->sortByDesc('average')->first();
    }
}