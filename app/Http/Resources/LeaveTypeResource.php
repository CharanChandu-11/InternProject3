<?php
// app/Http/Resources/LeaveTypeResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
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
            'name' => $this->name,
            'days_allowed' => $this->days_allowed,
            'applicable_for' => $this->applicable_for,
            'applicable_for_text' => $this->getApplicableForText(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Get human-readable text for applicable_for.
     */
    private function getApplicableForText(): string
    {
        return match($this->applicable_for) {
            'both' => 'Teachers & Employees',
            'teacher' => 'Teachers Only',
            'employee' => 'Employees Only',
            default => ucfirst(str_replace('_', ' ', $this->applicable_for)),
        };
    }
}