<?php
// app/Http/Resources/TransportRouteResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransportRouteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'route_name' => $this->route_name,
            'route_number' => $this->route_number,
            'description' => $this->description,
            'stops' => StopResource::collection($this->whenLoaded('stops')),
            'vehicles' => VehicleResource::collection($this->whenLoaded('vehicles')),
            'students_count' => $this->whenCounted('studentTransport'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}