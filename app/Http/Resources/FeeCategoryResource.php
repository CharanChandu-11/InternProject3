<?php
// app/Http/Resources/FeeCategoryResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeeCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'fee_structures_count' => $this->whenCounted('feeStructures'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}