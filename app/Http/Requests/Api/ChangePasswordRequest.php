<?php
// app/Http/Requests/Api/ChangePasswordRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
            'new_password_confirmation' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'new_password.different' => 'New password must be different from current password'
        ];
    }
}