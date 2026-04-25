<?php
// app/Http/Resources/SectionResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'capacity' => $this->capacity,
            'class' => new ClassResource($this->whenLoaded('class')),
            'students_count' => $this->whenCounted('students'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}