<?php
// app/Http/Controllers/Api/SuperAdmin/HostelController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Hostel;
use Illuminate\Http\Request;

class HostelController extends BaseController
{
    public function index()
    {
        $hostels = Hostel::with('rooms')->get();
        return $this->sendResponse($hostels, 'Hostels retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:boys,girls,co_ed',
            'warden_name' => 'required|string',
            'warden_phone' => 'required|string',
            'address' => 'required|string',
            'total_rooms' => 'required|integer|min:1',
        ]);
        $hostel = Hostel::create($validated);
        return $this->sendResponse($hostel, 'Hostel created', 201);
    }

    public function show(Hostel $hostel)
    {
        $hostel->load('rooms');
        return $this->sendResponse($hostel, 'Hostel retrieved');
    }

    public function update(Request $request, Hostel $hostel)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'type' => 'sometimes|in:boys,girls,co_ed',
            'warden_name' => 'sometimes|string',
            'warden_phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'total_rooms' => 'sometimes|integer|min:1',
        ]);
        $hostel->update($validated);
        return $this->sendResponse($hostel, 'Hostel updated');
    }

    public function destroy(Hostel $hostel)
    {
        $hostel->delete();
        return $this->sendResponse([], 'Hostel deleted');
    }

    public function rooms(Hostel $hostel)
    {
        $rooms = $hostel->rooms;
        return $this->sendResponse($rooms, 'Rooms retrieved');
    }
}