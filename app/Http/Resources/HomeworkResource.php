<?php
// app/Http/Resources/HomeworkResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'excerpt' => \Str::limit(strip_tags($this->description), 150),
            'class_id' => $this->class_id,
            'class_name' => $this->class?->name,
            'section_id' => $this->section_id,
            'section_name' => $this->section?->name,
            'subject_id' => $this->subject_id,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'teacher_id' => $this->teacher_id,
            'teacher' => new UserResource($this->whenLoaded('teacher')),
            'submission_date' => $this->submission_date?->toDateString(),
            'submission_date_formatted' => $this->submission_date?->format('F j, Y'),
            'submission_time' => $this->submission_time?->format('h:i A'),
            'attachments' => $this->attachments,
            'total_marks' => $this->total_marks,
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'is_expired' => $this->submission_date?->isPast(),
            'days_remaining' => $this->submission_date ? now()->diffInDays($this->submission_date, false) : null,
            'submissions_count' => $this->whenCounted('submissions'),
            'submissions' => HomeworkSubmissionResource::collection($this->whenLoaded('submissions')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'active' => 'success',
            'expired' => 'secondary',
            'draft' => 'warning',
            default => 'primary'
        };
    }
}