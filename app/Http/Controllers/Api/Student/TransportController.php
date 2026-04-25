<?php
// app/Http/Controllers/Api/Student/TransportController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\StudentTransport;
use App\Models\TransportRoute;
use App\Models\Stop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransportController extends BaseController
{
    /**
     * Get student's allocated transport details
     */
    public function index()
    {
        $student = Auth::user()->student;

        $transport = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->with(['route', 'stop', 'route.vehicles'])
            ->first();

        if (!$transport) {
            return $this->sendResponse(null, 'No transport allocated');
        }

        $vehicle = $transport->route->vehicles->first();

        $routeStops = $transport->route->stops()
            ->orderBy('pickup_time')
            ->get()
            ->map(fn($stop) => [
                'name' => $stop->stop_name,
                'pickup_time' => Carbon::parse($stop->pickup_time)->format('h:i A'),
                'drop_time' => Carbon::parse($stop->drop_time)->format('h:i A'),
                'latitude' => $stop->latitude,
                'longitude' => $stop->longitude,
            ]);

        return $this->sendResponse([
            'transport' => [
                'id' => $transport->id,
                'start_date' => $transport->start_date->toDateString(),
                'end_date' => $transport->end_date?->toDateString(),
                'is_active' => $transport->is_active,
            ],
            'route' => [
                'id' => $transport->route->id,
                'name' => $transport->route->route_name,
                'number' => $transport->route->route_number,
                'description' => $transport->route->description,
            ],
            'stop' => [
                'id' => $transport->stop->id,
                'name' => $transport->stop->stop_name,
                'pickup_time' => Carbon::parse($transport->stop->pickup_time)->format('h:i A'),
                'drop_time' => Carbon::parse($transport->stop->drop_time)->format('h:i A'),
                'monthly_fee' => $transport->stop->fee,
                'monthly_fee_formatted' => '₹ ' . number_format($transport->stop->fee, 2),
            ],
            'vehicle' => $vehicle ? [
                'number' => $vehicle->vehicle_number,
                'type' => $vehicle->vehicle_type,
                'model' => $vehicle->model,
                'capacity' => $vehicle->capacity,
                'driver_name' => $vehicle->driver_name,
                'driver_phone' => $vehicle->driver_phone,
            ] : null,
            'route_stops' => $routeStops,
        ], 'Transport details retrieved');
    }

    /**
     * Get all available transport routes for browsing
     */
    public function routes(Request $request)
    {
        $student = Auth::user()->student;

        $query = TransportRoute::with(['stops', 'vehicles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('route_name', 'like', "%{$search}%")
                  ->orWhere('route_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stop_id')) {
            $query->whereHas('stops', fn($q) => $q->where('id', $request->stop_id));
        }

        $routes = $query->orderBy('route_number')->paginate($request->per_page ?? 10);

        $stops = Stop::orderBy('stop_name')->get();

        $currentTransport = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->first();

        $formattedRoutes = $routes->getCollection()->map(fn($route) => [
            'id' => $route->id,
            'name' => $route->route_name,
            'number' => $route->route_number,
            'description' => $route->description,
            'stops' => $route->stops->map(fn($stop) => [
                'id' => $stop->id,
                'name' => $stop->stop_name,
                'pickup_time' => Carbon::parse($stop->pickup_time)->format('h:i A'),
                'drop_time' => Carbon::parse($stop->drop_time)->format('h:i A'),
                'fee' => $stop->fee,
                'fee_formatted' => '₹ ' . number_format($stop->fee, 2),
            ]),
            'vehicle' => $route->vehicles->first() ? [
                'number' => $route->vehicles->first()->vehicle_number,
                'type' => $route->vehicles->first()->vehicle_type,
            ] : null,
        ]);

        return $this->sendResponse([
            'routes' => $formattedRoutes,
            'available_stops' => $stops,
            'has_active_transport' => !is_null($currentTransport),
            'current_transport' => $currentTransport ? [
                'route_name' => $currentTransport->route->route_name,
                'stop_name' => $currentTransport->stop->stop_name,
            ] : null,
            'pagination' => [
                'current_page' => $routes->currentPage(),
                'last_page' => $routes->lastPage(),
                'per_page' => $routes->perPage(),
                'total' => $routes->total(),
            ],
        ], 'Available routes retrieved');
    }

    /**
     * Get detailed route information (for modal)
     */
    public function getRouteDetails(TransportRoute $route)
    {
        $route->load(['stops', 'vehicles']);

        $stops = $route->stops->map(fn($stop) => [
            'id' => $stop->id,
            'name' => $stop->stop_name,
            'pickup_time' => Carbon::parse($stop->pickup_time)->format('h:i A'),
            'drop_time' => Carbon::parse($stop->drop_time)->format('h:i A'),
            'fee' => $stop->fee,
            'fee_formatted' => '₹ ' . number_format($stop->fee, 2),
            'latitude' => $stop->latitude,
            'longitude' => $stop->longitude,
        ]);

        $vehicle = $route->vehicles->first();

        return $this->sendResponse([
            'route' => [
                'id' => $route->id,
                'name' => $route->route_name,
                'number' => $route->route_number,
                'description' => $route->description,
            ],
            'stops' => $stops,
            'vehicle' => $vehicle ? [
                'number' => $vehicle->vehicle_number,
                'type' => $vehicle->vehicle_type,
                'capacity' => $vehicle->capacity,
                'driver_name' => $vehicle->driver_name,
                'driver_phone' => $vehicle->driver_phone,
            ] : null,
        ], 'Route details retrieved');
    }

    /**
     * Request transport allocation (pending approval)
     */
    public function requestAllocation(Request $request)
    {
        $student = Auth::user()->student;

        $request->validate([
            'route_id' => 'required|exists:transport_routes,id',
            'stop_id' => 'required|exists:stops,id',
        ]);

        $existing = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return $this->sendError('You already have an active transport allocation', [], 422);
        }

        $transport = StudentTransport::create([
            'student_id' => $student->id,
            'transport_route_id' => $request->route_id,
            'stop_id' => $request->stop_id,
            'start_date' => now(),
            'is_active' => false, // Needs admin approval
        ]);

        // Notify admin (optional)
        // Notification::create(...);

        return $this->sendResponse([
            'request_id' => $transport->id,
            'message' => 'Transport request submitted. Waiting for approval.',
        ], 'Request submitted successfully');
    }

    /**
     * Get fare details for a specific stop
     */
    public function getFare($stopId)
    {
        $stop = Stop::findOrFail($stopId);

        return $this->sendResponse([
            'stop_id' => $stop->id,
            'stop_name' => $stop->stop_name,
            'fee' => $stop->fee,
            'fee_formatted' => '₹ ' . number_format($stop->fee, 2),
        ], 'Fare details retrieved');
    }
}