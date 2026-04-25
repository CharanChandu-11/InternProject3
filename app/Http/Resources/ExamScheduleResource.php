<?php
// app/Http/Resources/ExamScheduleResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExamScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'exam' => new ExamResource($this->whenLoaded('exam')),
            'class' => new ClassResource($this->whenLoaded('class')),
            'section' => new SectionResource($this->whenLoaded('section')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'exam_date' => $this->exam_date?->toDateString(),
            'exam_date_formatted' => $this->exam_date?->format('F j, Y'),
            'start_time' => $this->start_time?->format('h:i A'),
            'end_time' => $this->end_time?->format('h:i A'),
            'total_marks' => $this->total_marks,
            'passing_marks' => $this->passing_marks,
            'room_number' => $this->room_number,
            'results_count' => $this->whenCounted('results'),
            'results' => ExamResultResource::collection($this->whenLoaded('results')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}