<?php
// app/Http/Resources/AttendanceResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->attendance_date->toDateString(),
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'check_in_time' => $this->check_in_time?->format('h:i A'),
            'check_out_time' => $this->check_out_time?->format('h:i A'),
            'remarks' => $this->remarks,
            'student' => new StudentResource($this->whenLoaded('attendable')),
            'marked_by' => new UserResource($this->whenLoaded('markedByUser')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'half_day' => 'info',
            default => 'secondary'
        };
    }
}