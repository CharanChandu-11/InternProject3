<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stop;
use App\Models\TransportRoute;
use Illuminate\Http\Request;

class StopController extends Controller
{
    public function index()
    {
        $stops = Stop::with('route')->orderBy('transport_route_id')->orderBy('sort_order')->paginate(30);
        return view('admin.stops.index', compact('stops'));
    }

    public function create()
    {
        $routes = TransportRoute::orderBy('route_number')->get();
        return view('admin.stops.create', compact('routes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transport_route_id' => 'required|exists:transport_routes,id',
            'stop_name' => 'required|string',
            'pickup_time' => 'required|date_format:H:i',
            'drop_time' => 'required|date_format:H:i|after:pickup_time',
            'fee' => 'required|numeric|min:0',
            'distance_from_previous' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);
        Stop::create($request->all());
        return redirect()->route('admin.stops.index')->with('success', 'Stop added.');
    }

    public function edit(Stop $stop)
    {
        $routes = TransportRoute::orderBy('route_number')->get();
        return view('admin.stops.edit', compact('stop', 'routes'));
    }

    public function update(Request $request, Stop $stop)
    {
        $request->validate([
            'transport_route_id' => 'required|exists:transport_routes,id',
            'stop_name' => 'required|string',
            'pickup_time' => 'required|date_format:H:i',
            'drop_time' => 'required|date_format:H:i|after:pickup_time',
            'fee' => 'required|numeric|min:0',
            'distance_from_previous' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);
        $stop->update($request->all());
        return redirect()->route('admin.stops.index')->with('success', 'Stop updated.');
    }

    public function destroy(Stop $stop)
    {
        $stop->delete();
        return redirect()->route('admin.stops.index')->with('success', 'Stop deleted.');
    }
}