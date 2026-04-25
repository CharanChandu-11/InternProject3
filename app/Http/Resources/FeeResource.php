<?php
// app/Http/Resources/FeeResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'fee_structure' => new FeeStructureResource($this->whenLoaded('feeStructure')),
            'total_amount' => $this->total_amount,
            'total_amount_formatted' => '₹ ' . number_format($this->total_amount, 2),
            'paid_amount' => $this->paid_amount,
            'paid_amount_formatted' => '₹ ' . number_format($this->paid_amount, 2),
            'due_amount' => $this->due_amount,
            'due_amount_formatted' => '₹ ' . number_format($this->due_amount, 2),
            'due_date' => $this->due_date?->toDateString(),
            'due_date_formatted' => $this->due_date?->format('F j, Y'),
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'is_overdue' => $this->due_date?->isPast() && $this->status !== 'paid',
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'paid' => 'success',
            'partial' => 'warning',
            'pending' => 'danger',
            'overdue' => 'dark',
            default => 'secondary'
        };
    }
}