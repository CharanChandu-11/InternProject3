<?php
// app/Http/Resources/VehicleResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vehicle_number' => $this->vehicle_number,
            'vehicle_type' => $this->vehicle_type,
            'model' => $this->model,
            'capacity' => $this->capacity,
            'driver_name' => $this->driver_name,
            'driver_license' => $this->driver_license,
            'driver_phone' => $this->driver_phone,
            'insurance_expiry' => $this->insurance_expiry?->toDateString(),
            'insurance_expiry_formatted' => $this->insurance_expiry?->format('F j, Y'),
            'is_insurance_valid' => $this->insurance_expiry?->isFuture(),
            'routes' => TransportRouteResource::collection($this->whenLoaded('routes')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}