<?php
// app/Http/Resources/HomeworkSubmissionResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkSubmissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'homework_id' => $this->homework_id,
            'homework' => new HomeworkResource($this->whenLoaded('homework')),
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'submission_text' => $this->submission_text,
            'attachments' => $this->attachments,
            'submitted_at' => $this->submitted_at?->toDateTimeString(),
            'submitted_at_formatted' => $this->submitted_at?->diffForHumans(),
            'is_late' => $this->is_late,
            'obtained_marks' => $this->obtained_marks,
            'feedback' => $this->feedback,
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'submitted' => 'info',
            'late' => 'warning',
            'graded' => 'success',
            default => 'secondary'
        };
    }
}