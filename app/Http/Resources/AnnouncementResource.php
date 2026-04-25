<?php
// app/Http/Resources/AnnouncementResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;


class AnnouncementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => Str::limit(strip_tags($this->content), 150),
            'audience' => $this->audience,
            'audience_text' => $this->getAudienceText(),
            'specific_classes' => $this->specific_classes,
            'publish_date' => $this->publish_date?->toDateString(),
            'publish_date_formatted' => $this->publish_date?->format('F j, Y'),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'expiry_date_formatted' => $this->expiry_date?->format('F j, Y'),
            'is_published' => $this->is_published,
            'is_active' => $this->isPublished(),
            'status' => $this->getStatus(),
            'status_color' => $this->getStatusColor(),
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'attachments' => $this->attachments,
            'views_count' => $this->views_count ?? 0,
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function getAudienceText()
    {
        $audiences = [
            'all' => 'Everyone',
            'students' => 'Students Only',
            'parents' => 'Parents Only',
            'teachers' => 'Teachers Only',
            'employees' => 'Employees Only',
            'specific_classes' => 'Specific Classes'
        ];

        return $audiences[$this->audience] ?? ucfirst($this->audience);
    }

    private function isPublished()
    {
        return $this->is_published && 
               $this->publish_date <= now() && 
               (!$this->expiry_date || $this->expiry_date >= now());
    }

    private function getStatus()
    {
        if (!$this->is_published) {
            return 'Draft';
        }
        
        if ($this->publish_date > now()) {
            return 'Scheduled';
        }
        
        if ($this->expiry_date && $this->expiry_date < now()) {
            return 'Expired';
        }
        
        return 'Published';
    }

    private function getStatusColor()
    {
        return match($this->getStatus()) {
            'Published' => 'success',
            'Scheduled' => 'info',
            'Draft' => 'warning',
            'Expired' => 'secondary',
            default => 'primary'
        };
    }
}