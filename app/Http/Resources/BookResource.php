<?php
// app/Http/Resources/BookResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'publication_year' => $this->publication_year,
            'category' => $this->category,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'shelf_location' => $this->shelf_location,
            'description' => $this->description,
            'is_available' => $this->isAvailable(),
            'availability_status' => $this->getAvailabilityStatus(),
            'availability_color' => $this->getAvailabilityColor(),
            'issues_count' => $this->whenCounted('issues'),
            'issues' => BookIssueResource::collection($this->whenLoaded('issues')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function isAvailable()
    {
        return $this->available_quantity > 0;
    }

    private function getAvailabilityStatus()
    {
        if ($this->available_quantity == 0) {
            return 'Not Available';
        }
        if ($this->available_quantity < 5) {
            return 'Low Stock';
        }
        return 'Available';
    }

    private function getAvailabilityColor()
    {
        return match($this->getAvailabilityStatus()) {
            'Available' => 'success',
            'Low Stock' => 'warning',
            'Not Available' => 'danger',
            default => 'secondary'
        };
    }
}