<?php
// app/Http/Controllers/Student/TransportController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentTransport;
use App\Models\TransportRoute;
use App\Models\Stop;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransportController extends Controller
{
    /**
     * Display student's allocated transport details
     */
    public function index()
    {
        $student = Auth::user()->student;
        
        $transport = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->with(['route', 'stop', 'route.vehicles'])
            ->first();
        
        if (!$transport) {
            return view('student.transport.index', compact('transport'));
        }
        
        // Get vehicle assigned to this route
        $vehicle = $transport->route->vehicles->first();
        
        // Calculate days until pickup/drop
        $today = Carbon::today();
        $pickupTime = Carbon::parse($transport->stop->pickup_time);
        $dropTime = Carbon::parse($transport->stop->drop_time);
        
        // Get route map coordinates (if available)
        $routeStops = $transport->route->stops()
            ->orderBy('pickup_time')
            ->get()
            ->map(function($stop) {
                return [
                    'name' => $stop->stop_name,
                    'pickup_time' => $stop->pickup_time->format('h:i A'),
                    'drop_time' => $stop->drop_time->format('h:i A'),
                    'latitude' => $stop->latitude,
                    'longitude' => $stop->longitude,
                ];
            });
        
        // Calculate total monthly fee
        $monthlyFee = $transport->stop->fee;
        
        // Get attendance for transport (if implemented)
        $attendance = null; // You can implement transport attendance tracking
        
        return view('student.transport.index', compact(
            'transport', 
            'vehicle', 
            'pickupTime', 
            'dropTime', 
            'routeStops', 
            'monthlyFee',
            'attendance'
        ));
    }
    
    /**
     * Display all available transport routes for browsing
     */
    public function routes(Request $request)
    {
        $student = Auth::user()->student;
        
        $query = TransportRoute::with(['stops', 'vehicles']);
        
        // Search by route name or number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('route_name', 'like', "%{$search}%")
                  ->orWhere('route_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by stop
        if ($request->filled('stop_id')) {
            $query->whereHas('stops', function($q) use ($request) {
                $q->where('id', $request->stop_id);
            });
        }
        
        $routes = $query->orderBy('route_number')
            ->paginate(10)
            ->appends($request->query());
        
        // Get all stops for filter
        $stops = Stop::orderBy('stop_name')->get();
        
        // Get student's current allocated route (if any)
        $currentTransport = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->first();
        
        return view('student.transport.routes', compact('routes', 'stops', 'currentTransport'));
    }
    
    /**
     * Get route details for AJAX request
     */
    public function getRouteDetails(TransportRoute $route)
    {
        $route->load(['stops', 'vehicles']);
        
        $stops = $route->stops->map(function($stop) {
            return [
                'id' => $stop->id,
                'name' => $stop->stop_name,
                'pickup_time' => $stop->pickup_time->format('h:i A'),
                'drop_time' => $stop->drop_time->format('h:i A'),
                'fee' => $stop->fee,
                'fee_formatted' => '₹ ' . number_format($stop->fee, 2),
                'latitude' => $stop->latitude,
                'longitude' => $stop->longitude,
            ];
        });
        
        $vehicle = $route->vehicles->first();
        
        return response()->json([
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
        ]);
    }
    
    /**
     * Request transport allocation
     */
    public function requestAllocation(Request $request)
    {
        $student = Auth::user()->student;
        
        $request->validate([
            'route_id' => 'required|exists:transport_routes,id',
            'stop_id' => 'required|exists:stops,id',
        ]);
        
        // Check if student already has active transport
        $existing = StudentTransport::where('student_id', $student->id)
            ->where('is_active', true)
            ->first();
        
        if ($existing) {
            return redirect()->back()->with('error', 'You already have an active transport allocation.');
        }
        
        // Create transport request (pending approval)
        $transport = StudentTransport::create([
            'student_id' => $student->id,
            'transport_route_id' => $request->route_id,
            'stop_id' => $request->stop_id,
            'start_date' => now(),
            'is_active' => false, // Needs admin approval
        ]);
        
        // Notify admin (you can implement notification)
        
        return redirect()->route('student.transport')
            ->with('success', 'Transport request submitted. Waiting for approval.');
    }
    
    /**
     * Get estimated fare for a stop
     */
    public function getFare($stopId)
    {
        $stop = Stop::findOrFail($stopId);
        
        return response()->json([
            'stop_name' => $stop->stop_name,
            'fee' => $stop->fee,
            'fee_formatted' => '₹ ' . number_format($stop->fee, 2),
        ]);
    }
}