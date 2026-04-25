<?php
// app/Http/Controllers/Api/SuperAdmin/TransportRouteController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\TransportRoute;
use Illuminate\Http\Request;

class TransportRouteController extends BaseController
{
    public function index()
    {
        $routes = TransportRoute::with('stops', 'vehicles')->get();
        return $this->sendResponse($routes, 'Routes retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_name' => 'required|string',
            'route_number' => 'required|unique:transport_routes',
            'description' => 'nullable|string',
        ]);
        $route = TransportRoute::create($validated);
        return $this->sendResponse($route, 'Route created', 201);
    }

    public function show(TransportRoute $transportRoute)
    {
        $transportRoute->load('stops', 'vehicles');
        return $this->sendResponse($transportRoute, 'Route retrieved');
    }

    public function update(Request $request, TransportRoute $transportRoute)
    {
        $validated = $request->validate([
            'route_name' => 'sometimes|string',
            'route_number' => 'sometimes|unique:transport_routes,route_number,' . $transportRoute->id,
            'description' => 'nullable|string',
        ]);
        $transportRoute->update($validated);
        return $this->sendResponse($transportRoute, 'Route updated');
    }

    public function destroy(TransportRoute $transportRoute)
    {
        $transportRoute->delete();
        return $this->sendResponse([], 'Route deleted');
    }

    public function students(TransportRoute $transportRoute)
    {
        $students = $transportRoute->studentTransport()->with('student.user')->get();
        return $this->sendResponse($students, 'Students retrieved');
    }
}