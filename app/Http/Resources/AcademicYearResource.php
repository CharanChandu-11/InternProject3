<?php
// app/Http/Resources/AcademicYearResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_current' => $this->is_current,
            'status' => $this->is_current ? 'Current' : 'Past',
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}