<?php
// app/Http/Requests/Api/SuperAdmin/UpdateGalleryRequest.php

namespace App\Http\Requests\Api\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:10240',
            'category' => 'sometimes|string|max:100',
            'event_id' => 'nullable|exists:events,id',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer'
        ];
    }
}