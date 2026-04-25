<?php
// app/Http/Resources/SyllabusResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SyllabusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'class' => new ClassResource($this->whenLoaded('class')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'status' => $this->status,
            'publish_date' => $this->publish_date?->toDateString(),
            'topics' => SyllabusTopicResource::collection($this->whenLoaded('topics')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}