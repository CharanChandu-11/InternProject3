<?php
// app/Http/Resources/StopResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StopResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'route' => new TransportRouteResource($this->whenLoaded('route')),
            'stop_name' => $this->stop_name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'pickup_time' => $this->pickup_time?->format('h:i A'),
            'drop_time' => $this->drop_time?->format('h:i A'),
            'fee' => $this->fee,
            'fee_formatted' => '₹ ' . number_format($this->fee, 2),
            'students_count' => $this->whenCounted('studentTransport'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}