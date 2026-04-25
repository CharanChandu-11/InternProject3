<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(LoginRequest $request)
    {
        try {
            // Validate request
            $validated = $request->validated();

            // Determine login type (email or username)
            $loginType = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            
            $credentials = [
                $loginType => $validated['login'],
                'password' => $validated['password'],
            ];

            // Attempt login without is_active check first to avoid revealing account status
            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'login' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = Auth::user();
            
            // Check if account is active after successful credentials
            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'login' => ['Your account is inactive. Please contact support.'],
                ]);
            }
            
            // Revoke old tokens (optional - you might want to keep multiple sessions)
            if (config('auth.revoke_previous_tokens', true)) {
                $user->tokens()->delete();
            }
            
            // Create new token with abilities
            $token = $user->createToken('auth-token')->plainTextToken;
            
            
            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'module' => 'auth',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => json_encode(['login_type' => $loginType])
            ]);
            
            // Update last login info
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);

            // Prepare response data
            $responseData = [
                'success' => true,
                'message' => 'Successfully logged in.',
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ];

            // Add permissions and roles if using Laravel Permission
            if (method_exists($user, 'getAllPermissions')) {
                $responseData['permissions'] = $user->getAllPermissions()->pluck('name');
            }
            
            if (method_exists($user, 'getRoleNames')) {
                $responseData['roles'] = $user->getRoleNames();
            }

            return response()->json($responseData, 200);

        } catch (\Exception $e) {
    
            return response()->json([
                'success' => false,
                'message' => 'Login failed.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function getUserAbilities($user): array
    {
        // Define abilities based on user type
        $abilities = ['basic-access'];
        
        switch ($user->user_type) {
            case 'admin':
                $abilities[] = 'admin-access';
                $abilities[] = 'full-access';
                break;
            case 'manager':
                $abilities[] = 'manager-access';
                break;
            default:
                $abilities[] = 'user-access';
        }
        
        return $abilities;
    }

    /**
     * Logout user (Revoke token)
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'module' => 'auth',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Revoke token
        $accessToken = $request->user()->currentAccessToken();
        if ($accessToken && isset($accessToken->id)) {
            $request->user()->tokens()->where('id', $accessToken->id)->delete();
        } elseif ($request->user()) {
            $request->user()->tokens()->delete();
        }

        return $this->sendResponse([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 'Logged out successfully');
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user()->load(['profile', 'student.class', 'student.section', 'employee', 'parent.children']);
        
        return $this->sendResponse([
            'user' => new UserResource($user),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames()
        ], 'User retrieved successfully');
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        
        $user->update($request->only(['name', 'phone', 'address']));
        
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->update(['profile_photo' => $path]);
        }
        
        // Update profile if exists
        if ($user->profile) {
            $user->profile->update($request->only([
                'date_of_birth', 'gender', 'blood_group', 'emergency_contact'
            ]));
        }

        return $this->sendResponse(new UserResource($user->fresh()), 'Profile updated successfully');
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect', [], 422);
        }
        
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->sendResponse([], 'Password changed successfully');
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Reset link sent to your email'
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Unable to send reset link'
        ], 500);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // ✅ Validation fail JSON response
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->sendResponse([], 'Password reset successfully');
        }

        return $this->sendError('Invalid token', [], 422);
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasVerifiedEmail()) {
            return $this->sendError('Email already verified', [], 422);
        }
        
        $user->sendEmailVerificationNotification();

        return $this->sendResponse([], 'Verification link sent');
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        $user = User::find($request->id);
        
        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return $this->sendError('Invalid verification link', [], 403);
        }
        
        if ($user->hasVerifiedEmail()) {
            return $this->sendError('Email already verified', [], 422);
        }
        
        $user->markEmailAsVerified();

        return $this->sendResponse([], 'Email verified successfully');
    }

    protected function sendResponse($data, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

}