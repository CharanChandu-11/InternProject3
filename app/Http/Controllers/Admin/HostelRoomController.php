<?php
// app/Http/Controllers/Admin/HostelRoomController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelRoom;
use Illuminate\Http\Request;

class HostelRoomController extends Controller
{
    /**
     * Display a listing of hostel rooms.
     */
    public function index(Request $request)
    {
        $query = HostelRoom::with('hostel');

        // Filter by hostel
        if ($request->has('hostel_id') && $request->hostel_id) {
            $query->where('hostel_id', $request->hostel_id);
        }

        // Filter by room type
        if ($request->has('room_type') && $request->room_type) {
            $query->where('room_type', $request->room_type);
        }

        // Search by room number
        if ($request->has('search') && $request->search) {
            $query->where('room_number', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->paginate(15);
        $hostels = Hostel::all();

        return view('admin.hostel-rooms.index', compact('rooms', 'hostels'));
    }

    /**
     * Show the form for creating a new hostel room.
     */
    public function create()
    {
        $hostels = Hostel::all();
        return view('admin.hostel-rooms.create', compact('hostels'));
    }

    /**
     * Store a newly created hostel room.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'room_number' => 'required|string|max:50',
            'room_type' => 'required|in:single,double,triple,dormitory',
            'capacity' => 'required|integer|min:1',
            'fee_per_month' => 'required|numeric|min:0',
        ]);

        // Check if room number already exists in this hostel
        $exists = HostelRoom::where('hostel_id', $validated['hostel_id'])
            ->where('room_number', $validated['room_number'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Room number already exists in this hostel.')
                ->withInput();
        }

        HostelRoom::create($validated);

        return redirect()->route('admin.hostel-rooms.index')
            ->with('success', 'Hostel room created successfully.');
    }

    /**
     * Display the specified hostel room.
     */
    public function show(HostelRoom $hostelRoom)
    {
        $hostelRoom->load(['hostel', 'allocations.student.user']);
        $availableSeats = $hostelRoom->capacity - $hostelRoom->occupied;

        return view('admin.hostel-rooms.show', compact('hostelRoom', 'availableSeats'));
    }

    /**
     * Show the form for editing the specified hostel room.
     */
    public function edit(HostelRoom $hostelRoom)
    {
        $hostels = Hostel::all();
        return view('admin.hostel-rooms.edit', compact('hostelRoom', 'hostels'));
    }

    /**
     * Update the specified hostel room.
     */
    public function update(Request $request, HostelRoom $hostelRoom)
    {
        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'room_number' => 'required|string|max:50',
            'room_type' => 'required|in:single,double,triple,dormitory',
            'capacity' => 'required|integer|min:' . $hostelRoom->occupied,
            'fee_per_month' => 'required|numeric|min:0',
        ]);

        // Check if room number already exists in this hostel (excluding current room)
        $exists = HostelRoom::where('hostel_id', $validated['hostel_id'])
            ->where('room_number', $validated['room_number'])
            ->where('id', '!=', $hostelRoom->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Room number already exists in this hostel.')
                ->withInput();
        }

        $hostelRoom->update($validated);

        return redirect()->route('admin.hostel-rooms.index')
            ->with('success', 'Hostel room updated successfully.');
    }

    /**
     * Remove the specified hostel room.
     */
    public function destroy(HostelRoom $hostelRoom)
    {
        if ($hostelRoom->occupied > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete room with occupied seats.');
        }

        $hostelRoom->delete();

        return redirect()->route('admin.hostel-rooms.index')
            ->with('success', 'Hostel room deleted successfully.');
    }
}