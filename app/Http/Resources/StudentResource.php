<?php
// app/Http/Resources/StudentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'admission_number' => $this->admission_number,
            'admission_date' => $this->admission_date?->toDateString(),
            'roll_number' => $this->roll_number,
            'class' => new ClassResource($this->whenLoaded('class')),
            'section' => new SectionResource($this->whenLoaded('section')),
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'user' => new UserResource($this->whenLoaded('user')),
            'parents' => ParentResource::collection($this->whenLoaded('parents')),
            'attendance_percentage' => $this->attendance_percentage,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}