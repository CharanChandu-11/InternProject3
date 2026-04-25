<?php
// app/Http/Resources/TimetableResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimetableResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'day' => $this->day_formatted,
            'day_lower' => $this->day_of_week,
            'time_range' => $this->time_range,
            'room_number' => $this->room_number,
            'class' => new ClassResource($this->whenLoaded('class')),
            'section' => new SectionResource($this->whenLoaded('section')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'teacher' => new UserResource($this->whenLoaded('teacher')),
            'time_slot' => [
                'id' => $this->timeSlot->id,
                'name' => $this->timeSlot->name,
                'start_time' => $this->timeSlot->start_time->format('h:i A'),
                'end_time' => $this->timeSlot->end_time->format('h:i A')
            ]
        ];
    }
}