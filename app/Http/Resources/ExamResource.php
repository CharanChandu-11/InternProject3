<?php
// app/Http/Resources/ExamResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'exam_type' => new ExamTypeResource($this->whenLoaded('examType')),
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'start_date' => $this->start_date?->toDateString(),
            'start_date_formatted' => $this->start_date?->format('F j, Y'),
            'end_date' => $this->end_date?->toDateString(),
            'end_date_formatted' => $this->end_date?->format('F j, Y'),
            'description' => $this->description,
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'schedules_count' => $this->whenCounted('schedules'),
            'schedules' => ExamScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'upcoming' => 'info',
            'ongoing' => 'success',
            'completed' => 'secondary',
            default => 'primary'
        };
    }
}