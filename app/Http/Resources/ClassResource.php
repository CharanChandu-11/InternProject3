<?php
// app/Http/Resources/ClassResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'numeric_name' => $this->numeric_name,
            'full_name' => $this->full_name,
            'capacity' => $this->capacity,
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'class_teacher' => new UserResource($this->whenLoaded('classTeacher')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'students_count' => $this->whenCounted('students'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}