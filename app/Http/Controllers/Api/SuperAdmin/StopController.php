<?php
// app/Http/Controllers/Api/SuperAdmin/StopController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Stop;
use Illuminate\Http\Request;

class StopController extends BaseController
{
    public function index()
    {
        $stops = Stop::with('route')->get();
        return $this->sendResponse($stops, 'Stops retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transport_route_id' => 'required|exists:transport_routes,id',
            'stop_name' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pickup_time' => 'required',
            'drop_time' => 'required',
            'fee' => 'required|numeric|min:0',
        ]);
        $stop = Stop::create($validated);
        return $this->sendResponse($stop, 'Stop created', 201);
    }

    public function show(Stop $stop)
    {
        $stop->load('route');
        return $this->sendResponse($stop, 'Stop retrieved');
    }

    public function update(Request $request, Stop $stop)
    {
        $validated = $request->validate([
            'transport_route_id' => 'sometimes|exists:transport_routes,id',
            'stop_name' => 'sometimes|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pickup_time' => 'sometimes',
            'drop_time' => 'sometimes',
            'fee' => 'sometimes|numeric|min:0',
        ]);
        $stop->update($validated);
        return $this->sendResponse($stop, 'Stop updated');
    }

    public function destroy(Stop $stop)
    {
        $stop->delete();
        return $this->sendResponse([], 'Stop deleted');
    }
}