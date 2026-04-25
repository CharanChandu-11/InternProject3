<?php
// app/Http/Controllers/Api/Teacher/ProfileController.php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends BaseController
{
    public function show()
    {
        $user = Auth::user();
        $user->load(['profile', 'employee']);
        
        return $this->sendResponse($user, 'Profile retrieved');
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'qualification' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        $user->update($request->only(['name', 'email', 'phone', 'address']));
        
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->profile_photo = $path;
            $user->save();
        }
        
        if ($user->profile) {
            $user->profile->update($request->only(['date_of_birth', 'gender', 'qualification']));
        } else {
            UserProfile::create(array_merge(
                $request->only(['date_of_birth', 'gender', 'qualification']),
                ['user_id' => $user->id]
            ));
        }
        
        if ($user->employee) {
            $user->employee->update($request->only(['qualification']));
        }
        
        return $this->sendResponse($user->fresh(['profile', 'employee']), 'Profile updated');
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect', [], 422);
        }
        
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return $this->sendResponse([], 'Password changed successfully');
    }
}