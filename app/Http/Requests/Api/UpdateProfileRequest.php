<?php
// app/Http/Requests/Api/UpdateProfileRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'profile_photo' => 'sometimes|image|max:2048',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'blood_group' => 'sometimes|string|max:10',
            'emergency_contact' => 'sometimes|string|max:20'
        ];
    }
}