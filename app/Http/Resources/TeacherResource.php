<?php
// app/Http/Resources/TeacherResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'qualification' => $this->qualification,
            'specialization' => $this->specialization,
            'experience_years' => $this->experience_years,
            'joining_date' => $this->joining_date?->toDateString(),
            'subjects' => SubjectResource::collection($this->whenLoaded('subjects')),
            'classes' => ClassResource::collection($this->whenLoaded('classes')),
            'is_class_teacher' => $this->is_class_teacher,
            'class_teacher_of' => new ClassResource($this->whenLoaded('classTeacherOf')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}