<?php
// app/Http/Resources/TaskResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'assigned_by' => new UserResource($this->whenLoaded('assignedBy')),
            'due_date' => $this->due_date?->toDateString(),
            'due_date_formatted' => $this->due_date?->format('F j, Y'),
            'priority' => $this->priority,
            'priority_text' => ucfirst($this->priority),
            'priority_color' => $this->priority_color,
            'status' => $this->status,
            'status_text' => ucfirst(str_replace('_', ' ', $this->status)),
            'status_color' => $this->status_color,
            'is_overdue' => $this->isOverdue(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'completed_at_formatted' => $this->completed_at?->diffForHumans(),
            'remarks' => $this->remarks,
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}