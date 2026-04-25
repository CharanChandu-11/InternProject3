<?php
// app/Http/Controllers/Student/HostelController.php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\HostelAllocation;
use App\Models\Hostel;
use App\Models\HostelRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HostelController extends Controller
{
    /**
     * Display student's hostel allocation details
     */
    public function index()
    {
        $student = Auth::user()->student;
        
        $allocation = HostelAllocation::where('student_id', $student->id)
            ->where('status', 'active')
            ->with(['room.hostel', 'room'])
            ->first();
        
        if (!$allocation) {
            // Show available hostels for new allocation
            $hostels = Hostel::with(['rooms' => function($q) {
                $q->where('occupied', '<', 'capacity');
            }])->get();
            
            return view('student.hostel.index', compact('allocation', 'hostels'));
        }
        
        // Calculate days remaining in current allocation
        $daysRemaining = null;
        if ($allocation->leave_date) {
            $daysRemaining = Carbon::today()->diffInDays($allocation->leave_date, false);
            if ($daysRemaining < 0) {
                $daysRemaining = 0;
            }
        }
        
        // Get roommates if any
        $roommates = HostelAllocation::where('hostel_room_id', $allocation->room_id)
            ->where('student_id', '!=', $student->id)
            ->where('status', 'active')
            ->with('student.user')
            ->get();
        
        // Get monthly fee
        $monthlyFee = $allocation->room->fee_per_month;
        
        // Calculate total paid and due (if implemented)
        $totalPaid = 0; // You can implement payment tracking
        $totalDue = $monthlyFee; // Simple calculation
        
        // Get amenities
        $amenities = $this->getHostelAmenities($allocation->room->hostel);
        
        // Get recent activities (you can implement activity log)
        $recentActivities = [
            ['date' => $allocation->allocation_date, 'description' => 'Room allocated'],
        ];
        
        return view('student.hostel.index', compact(
            'allocation',
            'roommates',
            'monthlyFee',
            'totalPaid',
            'totalDue',
            'amenities',
            'daysRemaining',
            'recentActivities'
        ));
    }
    
    /**
     * Get available rooms in a hostel
     */
    public function availableRooms(Hostel $hostel, Request $request)
    {
        $student = Auth::user()->student;
        
        // Check if student already has an active allocation
        $existingAllocation = HostelAllocation::where('student_id', $student->id)
            ->where('status', 'active')
            ->first();
        
        if ($existingAllocation) {
            return redirect()->route('student.hostel')
                ->with('error', 'You already have an active hostel allocation.');
        }
        
        $rooms = HostelRoom::where('hostel_id', $hostel->id)
            ->whereRaw('occupied < capacity')
            ->with('hostel')
            ->orderBy('room_number')
            ->get()
            ->map(function($room) {
                $availableSeats = $room->capacity - $room->occupied;
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $room->room_type,
                    'room_type_text' => ucfirst(str_replace('_', ' ', $room->room_type)),
                    'capacity' => $room->capacity,
                    'occupied' => $room->occupied,
                    'available_seats' => $availableSeats,
                    'fee_per_month' => $room->fee_per_month,
                    'fee_per_month_formatted' => '₹ ' . number_format($room->fee_per_month, 2),
                    'is_available' => $availableSeats > 0,
                ];
            });
        
        $hostel->load(['rooms']);
        
        return view('student.hostel.available-rooms', compact('hostel', 'rooms'));
    }
    
    /**
     * Request hostel allocation
     */
    /**
     * Request hostel allocation
     */
    public function requestAllocation(Request $request)
    {
        $student = Auth::user()->student;
        
        $request->validate([
            'room_id' => 'required|exists:hostel_rooms,id',
        ]);
        
        // Check if student already has active or pending allocation
        $existing = HostelAllocation::where('student_id', $student->id)
            ->whereIn('status', [HostelAllocation::STATUS_ACTIVE, HostelAllocation::STATUS_PENDING])
            ->first();
        
        if ($existing) {
            if ($existing->status == HostelAllocation::STATUS_ACTIVE) {
                return redirect()->back()->with('error', 'You already have an active hostel allocation.');
            } else {
                return redirect()->back()->with('error', 'You already have a pending hostel allocation request.');
            }
        }
        
        $room = HostelRoom::find($request->room_id);
        
        if ($room->occupied >= $room->capacity) {
            return redirect()->back()->with('error', 'This room is no longer available.');
        }
        
        // Create allocation request with 'pending' status
        $allocation = HostelAllocation::create([
            'student_id' => $student->id,
            'hostel_room_id' => $request->room_id,
            'allocation_date' => now(),
            'status' => HostelAllocation::STATUS_PENDING, // Use constant
        ]);
        
        // Increment occupied count
        $room->increment('occupied');
        
        // Notify admin (implement notification)
        
        return redirect()->route('student.hostel')
            ->with('success', 'Hostel allocation request submitted successfully. Waiting for approval.');
    }
    
    /**
     * Get hostel amenities
     */
    private function getHostelAmenities($hostel)
    {
        // You can store amenities in database or return static list
        $amenities = [
            '24/7 Security',
            'Wi-Fi Connectivity',
            'Power Backup',
            'Water Supply',
            'Mess Facility',
            'Common Room',
            'Study Room',
            'Recreation Area',
            'Laundry Service',
            'Medical Facility',
        ];
        
        return $amenities;
    }
    
    /**
     * Get all hostels for browsing
     */
    public function allHostels()
    {
        $hostels = Hostel::withCount(['rooms as total_rooms'])
            ->withCount(['rooms as available_rooms' => function($q) {
                $q->whereRaw('occupied < capacity');
            }])
            ->get()
            ->map(function($hostel) {
                $hostel->available_rooms = $hostel->available_rooms ?? 0;
                return $hostel;
            });
        
        return view('student.hostel.all-hostels', compact('hostels'));
    }
    
    /**
     * Get room details
     */
    public function roomDetails(HostelRoom $room)
    {
        $room->load(['hostel', 'allocations.student.user']);
        
        $currentOccupants = $room->allocations()
            ->where('status', 'active')
            ->with('student.user')
            ->get();
        
        return response()->json([
            'room' => [
                'id' => $room->id,
                'number' => $room->room_number,
                'type' => $room->room_type,
                'type_text' => ucfirst(str_replace('_', ' ', $room->room_type)),
                'capacity' => $room->capacity,
                'occupied' => $room->occupied,
                'available_seats' => $room->capacity - $room->occupied,
                'fee_per_month' => $room->fee_per_month,
                'fee_formatted' => '₹ ' . number_format($room->fee_per_month, 2),
            ],
            'hostel' => [
                'id' => $room->hostel->id,
                'name' => $room->hostel->name,
                'type' => $room->hostel->type,
                'type_text' => ucfirst(str_replace('_', ' ', $room->hostel->type)),
                'warden_name' => $room->hostel->warden_name,
                'warden_phone' => $room->hostel->warden_phone,
                'address' => $room->hostel->address,
            ],
            'current_occupants' => $currentOccupants->map(function($allocation) {
                return [
                    'name' => $allocation->student->user->name,
                    'admission_number' => $allocation->student->admission_number,
                    'allocation_date' => $allocation->allocation_date->format('d M, Y'),
                ];
            }),
        ]);
    }
    
}