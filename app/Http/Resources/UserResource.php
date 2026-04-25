<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Whether to include sensitive data
     */
    private bool $includeSensitive = false;

    /**
     * Create a new resource instance with sensitive data option
     */
    public function __construct($resource, bool $includeSensitive = false)
    {
        parent::__construct($resource);
        $this->includeSensitive = $includeSensitive;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            // Basic information
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'user_type' => $this->user_type,
            'user_type_text' => $this->getUserTypeText(),
            'profile_photo' => $this->profile_photo_url ?? $this->getDefaultAvatar(),
            'profile_photo_url' => $this->getProfilePhotoUrl(),
            'is_active' => (bool) $this->is_active,
            'status' => $this->is_active ? 'active' : 'inactive',
            
            // Timestamps
            'last_login_at' => $this->last_login_at ? $this->last_login_at->diffForHumans() : null,
            'last_login_at_formatted' => $this->last_login_at?->toDateTimeString(),
            'last_login_ip' => $this->last_login_ip,
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->format('F j, Y'),
            'created_ago' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            
            // Metadata
            'email_verified' => !is_null($this->email_verified_at),
            'has_two_factor' => !is_null($this->two_factor_secret),
            'timezone' => $this->timezone ?? config('app.timezone'),
            'locale' => $this->locale ?? app()->getLocale(),
            
            // Relationships (conditionally loaded)
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
        ];

        // Include contact information (sensitive but necessary)
        if ($this->shouldIncludeContactInfo($request)) {
            $data['email'] = $this->email;
            $data['phone'] = $this->phone;
            $data['phone_verified'] = !is_null($this->phone_verified_at);
            $data['secondary_phone'] = $this->secondary_phone;
            $data['address'] = $this->address;
            $data['address_details'] = $this->getFormattedAddress();
        }

        // Include role and permission data if loaded
        if ($this->relationLoaded('roles')) {
            $data['roles'] = RoleResource::collection($this->whenLoaded('roles'));
            $data['role_names'] = $this->getRoleNames();
        }

        // Include user-type specific profiles
        $this->includeUserTypeProfiles($data);

        // Include sensitive data only for admin or self
        if ($this->includeSensitive || $this->canViewSensitiveData($request)) {
            $data['sensitive'] = $this->getSensitiveData();
        }

        // Include settings and preferences
        if ($this->relationLoaded('settings')) {
            $data['settings'] = new UserSettingResource($this->whenLoaded('settings'));
        }

        // Include statistics if requested
        if ($request->has('with_stats')) {
            $data['stats'] = $this->getUserStatistics();
        }

        return $data;
    }

    /**
     * Get user type display text
     */
    private function getUserTypeText(): string
    {
        $types = [
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            'staff' => 'Staff',
        ];

        return $types[$this->user_type] ?? ucfirst(str_replace('_', ' ', $this->user_type));
    }

    /**
     * Get formatted address
     */
    private function getFormattedAddress(): ?array
    {
        if (!$this->address) {
            return null;
        }

        // If address is stored as JSON
        if (is_string($this->address) && $this->isJson($this->address)) {
            return json_decode($this->address, true);
        }

        // If address is simple string
        return ['full_address' => $this->address];
    }

    /**
     * Get profile photo URL
     */
    private function getProfilePhotoUrl(): ?string
    {
        if ($this->profile_photo) {
            return Storage::url($this->profile_photo);
        }

        return $this->getGravatarUrl();
    }

    /**
     * Get default avatar based on user type
     */
    private function getDefaultAvatar(): string
    {
        $defaults = [
            'admin' => '/images/defaults/admin-avatar.png',
            'teacher' => '/images/defaults/teacher-avatar.png',
            'student' => '/images/defaults/student-avatar.png',
        ];

        return $defaults[$this->user_type] ?? '/images/defaults/user-avatar.png';
    }

    /**
     * Get Gravatar URL
     */
    private function getGravatarUrl(): string
    {
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp";
    }

    /**
     * Include user-type specific profiles
     */
    private function includeUserTypeProfiles(array &$data): void
    {
        $typeProfiles = [
            'student' => StudentResource::class,
            'teacher' => TeacherResource::class,
            'parent' => ParentResource::class,
            'employee' => EmployeeResource::class,
        ];

        foreach ($typeProfiles as $type => $resourceClass) {
            if ($this->user_type === $type && $this->relationLoaded($type)) {
                $data[$type] = new $resourceClass($this->whenLoaded($type));
            }
        }
    }

    /**
     * Should include contact information
     */
    private function shouldIncludeContactInfo($request): bool
    {
        // Include if user is viewing their own profile
        if ($request->user() && $request->user()->id === $this->id) {
            return true;
        }

        // Include if user has permission to view contact info
        if ($request->user() && $request->user()->can('view contact info')) {
            return true;
        }

        // Include for admin users
        if ($request->user() && in_array($request->user()->user_type, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Can view sensitive data
     */
    private function canViewSensitiveData($request): bool
    {
        if (!$request->user()) {
            return false;
        }

        return $request->user()->id === $this->id 
            || $request->user()->can('view sensitive data')
            || in_array($request->user()->user_type, ['super_admin', 'admin']);
    }

    /**
     * Get sensitive data
     */
    private function getSensitiveData(): array
    {
        return [
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'phone_verified_at' => $this->phone_verified_at?->toDateTimeString(),
            'two_factor_enabled' => !is_null($this->two_factor_secret),
            'last_password_change' => $this->password_changed_at?->toDateTimeString(),
            'failed_login_attempts' => $this->failed_login_attempts ?? 0,
            'last_failed_login' => $this->last_failed_login_at?->toDateTimeString(),
            'sso_providers' => $this->whenLoaded('socialAccounts', function () {
                return $this->socialAccounts->pluck('provider');
            }),
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics(): array
    {
        $stats = [];

        switch ($this->user_type) {
            case 'student':
                $stats = [
                    'total_courses' => $this->enrollments()->count(),
                    'completed_courses' => $this->enrollments()->where('status', 'completed')->count(),
                    'average_grade' => $this->grades()->avg('score'),
                ];
                break;
            case 'teacher':
                $stats = [
                    'total_courses' => $this->taughtCourses()->count(),
                    'total_students' => $this->taughtCourses()->withCount('enrollments')->get()->sum('enrollments_count'),
                    'upcoming_classes' => $this->schedules()->where('start_time', '>', now())->count(),
                ];
                break;
        }

        return $stats;
    }

    /**
     * Check if string is JSON
     */
    private function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Create a new resource instance with sensitive data
     */
    public function withSensitiveData(): self
    {
        $this->includeSensitive = true;
        return $this;
    }

    /**
     * Get additional data to be included with the resource
     */
    public function with($request)
    {
        return [
            'meta' => [
                'api_version' => '1.0',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}