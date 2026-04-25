<?php
// app/Http/Resources/LeaveApplicationResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveApplicationResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'start_date' => $this->start_date?->toDateString(),
            'start_date_formatted' => $this->start_date?->format('F j, Y'),
            'end_date' => $this->end_date?->toDateString(),
            'end_date_formatted' => $this->end_date?->format('F j, Y'),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
            'approved_date' => $this->approved_date?->toDateString(),
            'approved_date_formatted' => $this->approved_date?->format('F j, Y'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->diffForHumans(),
        ];
    }

    /**
     * Get the status color for badges.
     */
    private function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }
}