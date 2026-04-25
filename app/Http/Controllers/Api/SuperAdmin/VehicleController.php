<?php
// app/Http/Controllers/Api/SuperAdmin/VehicleController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends BaseController
{
    public function index()
    {
        $vehicles = Vehicle::with('routes')->get();
        return $this->sendResponse($vehicles, 'Vehicles retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|unique:vehicles',
            'vehicle_type' => 'required|string',
            'model' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'driver_name' => 'required|string',
            'driver_license' => 'required|string',
            'driver_phone' => 'required|string',
            'insurance_expiry' => 'nullable|date',
        ]);
        $vehicle = Vehicle::create($validated);
        return $this->sendResponse($vehicle, 'Vehicle created', 201);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load('routes');
        return $this->sendResponse($vehicle, 'Vehicle retrieved');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'vehicle_number' => 'sometimes|string|unique:vehicles,vehicle_number,' . $vehicle->id,
            'vehicle_type' => 'sometimes|string',
            'model' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1',
            'driver_name' => 'sometimes|string',
            'driver_license' => 'sometimes|string',
            'driver_phone' => 'sometimes|string',
            'insurance_expiry' => 'nullable|date',
        ]);
        $vehicle->update($validated);
        return $this->sendResponse($vehicle, 'Vehicle updated');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return $this->sendResponse([], 'Vehicle deleted');
    }
}