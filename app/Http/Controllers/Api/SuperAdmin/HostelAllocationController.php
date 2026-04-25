<?php
// app/Http/Controllers/Api/SuperAdmin/HostelAllocationController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use Illuminate\Http\Request;

class HostelAllocationController extends BaseController
{
    public function index()
    {
        $allocations = HostelAllocation::with(['student.user', 'room.hostel'])->get();
        return $this->sendResponse($allocations, 'Allocations retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id',
            'allocation_date' => 'required|date',
            'leave_date' => 'nullable|date|after:allocation_date',
        ]);
        $room = HostelRoom::find($validated['hostel_room_id']);
        if ($room->occupied >= $room->capacity) {
            return $this->sendError('Room is full', [], 422);
        }
        // Check if student already has active allocation
        $existing = HostelAllocation::where('student_id', $validated['student_id'])
            ->where('status', 'active')
            ->first();
        if ($existing) {
            return $this->sendError('Student already has an active hostel allocation', [], 422);
        }
        $validated['status'] = 'active';
        $allocation = HostelAllocation::create($validated);
        $room->increment('occupied');
        return $this->sendResponse($allocation, 'Allocation created', 201);
    }

    public function show(HostelAllocation $hostelAllocation)
    {
        $hostelAllocation->load(['student.user', 'room.hostel']);
        return $this->sendResponse($hostelAllocation, 'Allocation retrieved');
    }

    public function update(Request $request, HostelAllocation $hostelAllocation)
    {
        $validated = $request->validate([
            'leave_date' => 'nullable|date|after:allocation_date',
            'status' => 'sometimes|in:active,inactive',
        ]);
        $oldStatus = $hostelAllocation->status;
        $hostelAllocation->update($validated);
        // If status changed from active to inactive, free the room seat
        if ($oldStatus == 'active' && $hostelAllocation->status == 'inactive') {
            $hostelAllocation->room->decrement('occupied');
        }
        return $this->sendResponse($hostelAllocation, 'Allocation updated');
    }

    public function destroy(HostelAllocation $hostelAllocation)
    {
        if ($hostelAllocation->status == 'active') {
            $hostelAllocation->room->decrement('occupied');
        }
        $hostelAllocation->delete();
        return $this->sendResponse([], 'Allocation deleted');
    }
}