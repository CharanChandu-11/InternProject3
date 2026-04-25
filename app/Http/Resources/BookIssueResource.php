<?php
// app/Http/Resources/BookIssueResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookIssueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'book' => new BookResource($this->whenLoaded('book')),
            'issuable_type' => $this->issuable_type,
            'issuable' => $this->whenLoaded('issuable', function() {
                if ($this->issuable_type === 'App\\Models\\Student') {
                    return new StudentResource($this->issuable);
                }
                return new UserResource($this->issuable);
            }),
            'issue_date' => $this->issue_date?->toDateString(),
            'issue_date_formatted' => $this->issue_date?->format('F j, Y'),
            'due_date' => $this->due_date?->toDateString(),
            'due_date_formatted' => $this->due_date?->format('F j, Y'),
            'return_date' => $this->return_date?->toDateString(),
            'return_date_formatted' => $this->return_date?->format('F j, Y'),
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'is_overdue' => $this->status === 'issued' && $this->due_date?->isPast(),
            'late_fee' => $this->late_fee,
            'late_fee_formatted' => $this->late_fee ? '₹ ' . number_format($this->late_fee, 2) : null,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'issued' => 'success',
            'returned' => 'secondary',
            'overdue' => 'danger',
            'lost' => 'dark',
            default => 'primary'
        };
    }
}