<?php
// app/Http/Resources/RoleResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name ?? ucfirst($this->name),
            'description' => $this->description,
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            
            // Load permissions if needed
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'permission_count' => $this->whenCounted('permissions'),
        ];
    }
}