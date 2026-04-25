<?php
// app/Http/Resources/ParentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'parent_type' => $this->parent_type,
            'occupation' => $this->occupation,
            'office_address' => $this->office_address,
            'office_phone' => $this->office_phone,
            // CORRECT: This is the relationship from ParentModel to User
            'user' => new UserResource($this->whenLoaded('user')),
            // CORRECT: This is the relationship from ParentModel to children
            'children' => StudentResource::collection($this->whenLoaded('children')),
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}