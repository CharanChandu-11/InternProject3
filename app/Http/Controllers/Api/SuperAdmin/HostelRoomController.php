<?php
// app/Http/Controllers/Api/SuperAdmin/HostelRoomController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\HostelRoom;
use Illuminate\Http\Request;

class HostelRoomController extends BaseController
{
    public function index()
    {
        $rooms = HostelRoom::with('hostel')->get();
        return $this->sendResponse($rooms, 'Hostel rooms retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'room_number' => 'required|string',
            'room_type' => 'required|in:single,double,triple,dormitory',
            'capacity' => 'required|integer|min:1',
            'fee_per_month' => 'required|numeric|min:0',
        ]);
        // Ensure room number is unique within the hostel
        $exists = HostelRoom::where('hostel_id', $validated['hostel_id'])
            ->where('room_number', $validated['room_number'])
            ->exists();
        if ($exists) {
            return $this->sendError('Room number already exists in this hostel', [], 422);
        }
        $validated['occupied'] = 0;
        $room = HostelRoom::create($validated);
        return $this->sendResponse($room, 'Hostel room created', 201);
    }

    public function show(HostelRoom $hostelRoom)
    {
        $hostelRoom->load('hostel');
        return $this->sendResponse($hostelRoom, 'Hostel room retrieved');
    }

    public function update(Request $request, HostelRoom $hostelRoom)
    {
        $validated = $request->validate([
            'room_number' => 'sometimes|string',
            'room_type' => 'sometimes|in:single,double,triple,dormitory',
            'capacity' => 'sometimes|integer|min:1',
            'fee_per_month' => 'sometimes|numeric|min:0',
        ]);
        if (isset($validated['room_number'])) {
            $exists = HostelRoom::where('hostel_id', $hostelRoom->hostel_id)
                ->where('room_number', $validated['room_number'])
                ->where('id', '!=', $hostelRoom->id)
                ->exists();
            if ($exists) {
                return $this->sendError('Room number already exists in this hostel', [], 422);
            }
        }
        $hostelRoom->update($validated);
        return $this->sendResponse($hostelRoom, 'Hostel room updated');
    }

    public function destroy(HostelRoom $hostelRoom)
    {
        if ($hostelRoom->occupied > 0) {
            return $this->sendError('Cannot delete room with occupied seats', [], 422);
        }
        $hostelRoom->delete();
        return $this->sendResponse([], 'Hostel room deleted');
    }
}