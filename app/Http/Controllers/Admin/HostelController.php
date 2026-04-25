<?php
// app/Http/Controllers/Admin/HostelController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelRoom;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    /**
     * Display a listing of hostels.
     */
    public function index(Request $request)
    {
        $query = Hostel::query();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('warden_name', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $hostels = $query->withCount('rooms')->paginate(15);

        return view('admin.hostels.index', compact('hostels'));
    }

    /**
     * Show the form for creating a new hostel.
     */
    public function create()
    {
        return view('admin.hostels.create');
    }

    /**
     * Store a newly created hostel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:boys,girls,co_ed',
            'warden_name' => 'required|string|max:255',
            'warden_phone' => 'required|string|max:20',
            'address' => 'required|string',
            'total_rooms' => 'nullable|integer|min:0',
        ]);

        Hostel::create($validated);

        return redirect()->route('admin.hostels.index')
            ->with('success', 'Hostel created successfully.');
    }

    /**
     * Display the specified hostel.
     */
    public function show(Hostel $hostel)
    {
        $hostel->load(['rooms' => function($q) {
            $q->withCount('allocations');
        }]);

        $totalSeats = $hostel->rooms->sum('capacity');
        $occupiedSeats = $hostel->rooms->sum('occupied');
        $availableSeats = $totalSeats - $occupiedSeats;

        return view('admin.hostels.show', compact('hostel', 'totalSeats', 'occupiedSeats', 'availableSeats'));
    }

    /**
     * Show the form for editing the specified hostel.
     */
    public function edit(Hostel $hostel)
    {
        return view('admin.hostels.edit', compact('hostel'));
    }

    /**
     * Update the specified hostel.
     */
    public function update(Request $request, Hostel $hostel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:boys,girls,co_ed',
            'warden_name' => 'required|string|max:255',
            'warden_phone' => 'required|string|max:20',
            'address' => 'required|string',
            'total_rooms' => 'nullable|integer|min:0',
        ]);

        $hostel->update($validated);

        return redirect()->route('admin.hostels.index')
            ->with('success', 'Hostel updated successfully.');
    }

    /**
     * Remove the specified hostel.
     */
    public function destroy(Hostel $hostel)
    {
        // Check if hostel has any rooms with active allocations
        if ($hostel->rooms()->where('occupied', '>', 0)->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete hostel with occupied rooms.');
        }

        $hostel->delete();

        return redirect()->route('admin.hostels.index')
            ->with('success', 'Hostel deleted successfully.');
    }

    /**
     * Get rooms for a specific hostel (AJAX)
     */
    public function rooms(Hostel $hostel)
    {
        $rooms = $hostel->rooms()->withCount('allocations')->get();
        return response()->json($rooms);
    }
}