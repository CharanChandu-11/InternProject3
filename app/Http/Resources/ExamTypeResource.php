<?php
// app/Http/Resources/ExamTypeResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExamTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'exams_count' => $this->whenCounted('exams'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}