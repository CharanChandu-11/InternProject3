<?php
// app/Http/Resources/PaymentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'student' => new StudentResource($this->whenLoaded('student')),
            'student_fee' => new FeeResource($this->whenLoaded('studentFee')),
            'amount' => $this->amount,
            'amount_formatted' => '₹ ' . number_format($this->amount, 2),
            'payment_method' => $this->payment_method,
            'payment_method_text' => ucfirst(str_replace('_', ' ', $this->payment_method)),
            'transaction_id' => $this->transaction_id,
            'payment_date' => $this->payment_date?->toDateString(),
            'payment_date_formatted' => $this->payment_date?->format('F j, Y'),
            'status' => $this->status,
            'status_text' => ucfirst($this->status),
            'status_color' => $this->getStatusColor(),
            'remarks' => $this->remarks,
            'received_by' => new UserResource($this->whenLoaded('receivedBy')),
            'receipt_url' => $this->receipt_url,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function getStatusColor()
    {
        return match($this->status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'secondary'
        };
    }
}