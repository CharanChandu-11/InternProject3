<?php
// app/Http/Resources/PermissionResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            'display_name' => $this->display_name ?? ucfirst(str_replace('_', ' ', $this->name)),
            'description' => $this->description,
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'module' => $this->module ?? $this->getModuleFromName(),
        ];
    }

    /**
     * Extract module from permission name
     */
    private function getModuleFromName(): string
    {
        $parts = explode('.', $this->name);
        return $parts[0] ?? 'general';
    }
}