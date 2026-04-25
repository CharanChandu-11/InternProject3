<?php
// app/Http/Requests/Api/SuperAdmin/StoreGalleryRequest.php

namespace App\Http\Requests\Api\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'category' => 'required|string|max:100',
            'event_id' => 'nullable|exists:events,id',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Image title is required',
            'image.required' => 'Please select an image to upload',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be jpeg, png, jpg, or gif',
            'image.max' => 'Image size must not exceed 10MB',
            'category.required' => 'Category is required'
        ];
    }
}