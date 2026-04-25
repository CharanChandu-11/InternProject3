<?php
// app/Http/Resources/EventResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'excerpt' => \Str::limit(strip_tags($this->description), 150),
            'type' => $this->type,
            'type_text' => ucfirst(str_replace('_', ' ', $this->type)),
            'type_color' => $this->getTypeColor(),
            'start_date' => $this->start_date?->toDateString(),
            'start_date_formatted' => $this->start_date?->format('F j, Y'),
            'end_date' => $this->end_date?->toDateString(),
            'end_date_formatted' => $this->end_date?->format('F j, Y'),
            'start_time' => $this->start_time?->format('h:i A'),
            'end_time' => $this->end_time?->format('h:i A'),
            'venue' => $this->venue,
            'audience' => $this->audience,
            'audience_text' => $this->getAudienceText(),
            'participants' => $this->participants,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'status' => $this->getStatus(),
            'status_color' => $this->getStatusColor(),
            'is_upcoming' => $this->start_date?->isFuture(),
            'is_ongoing' => $this->start_date?->isPast() && $this->end_date?->isFuture(),
            'is_past' => $this->end_date?->isPast(),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'gallery' => GalleryResource::collection($this->whenLoaded('gallery')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getTypeColor()
    {
        return match($this->type) {
            'sports' => 'success',
            'cultural' => 'info',
            'academic' => 'primary',
            'meeting' => 'warning',
            'holiday' => 'secondary',
            'field_trip' => 'danger',
            default => 'primary'
        };
    }

    private function getAudienceText()
    {
        $audiences = [
            'all' => 'Everyone',
            'students' => 'Students Only',
            'parents' => 'Parents Only',
            'teachers' => 'Teachers Only',
            'staff' => 'Staff Only'
        ];

        return $audiences[$this->audience] ?? ucfirst($this->audience);
    }

    private function getStatus()
    {
        if ($this->start_date?->isFuture()) {
            return 'Upcoming';
        }
        
        if ($this->start_date?->isPast() && $this->end_date?->isFuture()) {
            return 'Ongoing';
        }
        
        if ($this->end_date?->isPast()) {
            return 'Completed';
        }
        
        return 'Scheduled';
    }

    private function getStatusColor()
    {
        return match($this->getStatus()) {
            'Upcoming' => 'info',
            'Ongoing' => 'success',
            'Completed' => 'secondary',
            default => 'primary'
        };
    }
}