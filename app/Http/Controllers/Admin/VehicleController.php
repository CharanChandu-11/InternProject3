<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::orderBy('vehicle_number')->paginate(20);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_number' => 'required|string|max:50|unique:vehicles',
            'vehicle_type' => 'required|string',
            'model' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'driver_name' => 'required|string',
            'driver_license' => 'required|string',
            'driver_phone' => 'required|string',
            'insurance_expiry' => 'nullable|date',
        ]);
        Vehicle::create($request->all());
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle added.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('admin.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'vehicle_number' => 'required|string|max:50|unique:vehicles,vehicle_number,' . $vehicle->id,
            'vehicle_type' => 'required|string',
            'model' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'driver_name' => 'required|string',
            'driver_license' => 'required|string',
            'driver_phone' => 'required|string',
            'insurance_expiry' => 'nullable|date',
        ]);
        $vehicle->update($request->all());
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle updated.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle deleted.');
    }
}