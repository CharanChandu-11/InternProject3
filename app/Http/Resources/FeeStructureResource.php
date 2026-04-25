<?php
// app/Http/Resources/FeeStructureResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeeStructureResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'class' => new ClassResource($this->whenLoaded('class')),
            'fee_category' => new FeeCategoryResource($this->whenLoaded('feeCategory')),
            'amount' => $this->amount,
            'amount_formatted' => '₹ ' . number_format($this->amount, 2),
            'frequency' => $this->frequency,
            'frequency_text' => ucfirst(str_replace('_', ' ', $this->frequency)),
            'is_optional' => $this->is_optional,
            'is_mandatory' => !$this->is_optional,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}