<?php
// app/Http/Controllers/Api/Student/HostelController.php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\HostelAllocation;
use App\Models\Hostel;
use App\Models\HostelRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HostelController extends BaseController
{
    /**
     * Get student's hostel allocation details
     */
    public function index()
    {
        $student = Auth::user()->student;

        $allocation = HostelAllocation::where('student_id', $student->id)
            ->where('status', HostelAllocation::STATUS_ACTIVE)
            ->with(['room.hostel', 'room'])
            ->first();

        if (!$allocation) {
            // Show available hostels for new allocation
            $hostels = Hostel::with(['rooms' => function($q) {
                $q->whereRaw('occupied < capacity');
            }])->get();

            return $this->sendResponse([
                'has_allocation' => false,
                'available_hostels' => $hostels->map(fn($hostel) => [
                    'id' => $hostel->id,
                    'name' => $hostel->name,
                    'type' => $hostel->type,
                    'type_text' => ucfirst(str_replace('_', ' ', $hostel->type)),
                    'warden_name' => $hostel->warden_name,
                    'warden_phone' => $hostel->warden_phone,
                    'address' => $hostel->address,
                    'total_rooms' => $hostel->rooms->count(),
                    'available_rooms' => $hostel->rooms->filter(fn($r) => $r->available_seats > 0)->count(),
                ]),
            ], 'No active hostel allocation');
        }

        // Calculate days remaining
        $daysRemaining = null;
        if ($allocation->leave_date) {
            $daysRemaining = max(0, Carbon::today()->diffInDays($allocation->leave_date, false));
        }

        $monthlyFee = $allocation->room->fee_per_month;
        $amenities = $this->getHostelAmenities();

        $recentActivities = [
            [
                'date' => $allocation->allocation_date->toDateString(),
                'description' => 'Room allocated',
            ],
        ];

        $roommates = HostelAllocation::where('hostel_room_id', $allocation->room_id)
            ->where('student_id', '!=', $student->id)
            ->where('status', HostelAllocation::STATUS_ACTIVE)
            ->with(['student.user'])
            ->get();

        $roommatesData = $roommates->map(fn($rm) => [
            'id' => $rm->student->id,
            'name' => $rm->student->user->name,
            'admission_number' => $rm->student->admission_number,
            'profile_photo' => $rm->student->user->profile_photo_url,
            'allocation_date' => $rm->allocation_date->toDateString(),
        ]);

        return $this->sendResponse([
            'has_allocation' => true,
            'allocation' => [
                'id' => $allocation->id,
                'allocation_date' => $allocation->allocation_date->toDateString(),
                'leave_date' => $allocation->leave_date?->toDateString(),
                'days_remaining' => $daysRemaining,
                'status' => $allocation->status,
                'status_text' => $allocation->status_text,
                'status_color' => $allocation->status_color,
            ],
            'hostel' => [
                'id' => $allocation->room->hostel->id,
                'name' => $allocation->room->hostel->name,
                'type' => $allocation->room->hostel->type,
                'type_text' => ucfirst(str_replace('_', ' ', $allocation->room->hostel->type)),
                'warden_name' => $allocation->room->hostel->warden_name,
                'warden_phone' => $allocation->room->hostel->warden_phone,
                'address' => $allocation->room->hostel->address,
            ],
            'room' => [
                'id' => $allocation->room->id,
                'number' => $allocation->room->room_number,
                'type' => $allocation->room->room_type,
                'type_text' => ucfirst(str_replace('_', ' ', $allocation->room->room_type)),
                'capacity' => $allocation->room->capacity,
                'occupied' => $allocation->room->occupied,
                'available_seats' => $allocation->room->available_seats,
                'fee_per_month' => $monthlyFee,
                'fee_per_month_formatted' => '₹ ' . number_format($monthlyFee, 2),
            ],
            'roommates' => $roommatesData,
            'amenities' => $amenities,
            'recent_activities' => $recentActivities,
        ], 'Hostel allocation details');
    }

    /**
     * Get available rooms in a specific hostel
     */
    public function availableRooms(Hostel $hostel)
    {
        $student = Auth::user()->student;

        // Check if student already has active or pending allocation
        $existingAllocation = HostelAllocation::where('student_id', $student->id)
            ->whereIn('status', [HostelAllocation::STATUS_ACTIVE, HostelAllocation::STATUS_PENDING])
            ->first();

        if ($existingAllocation) {
            return $this->sendError(
                'You already have a ' . $existingAllocation->status . ' hostel allocation',
                ['current_status' => $existingAllocation->status],
                422
            );
        }

        $rooms = HostelRoom::where('hostel_id', $hostel->id)
            ->whereRaw('occupied < capacity')
            ->with('hostel')
            ->orderBy('room_number')
            ->get()
            ->map(fn($room) => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type,
                'room_type_text' => ucfirst(str_replace('_', ' ', $room->room_type)),
                'capacity' => $room->capacity,
                'occupied' => $room->occupied,
                'available_seats' => $room->available_seats,
                'fee_per_month' => $room->fee_per_month,
                'fee_per_month_formatted' => '₹ ' . number_format($room->fee_per_month, 2),
                'is_available' => $room->available_seats > 0,
            ]);

        return $this->sendResponse([
            'hostel' => [
                'id' => $hostel->id,
                'name' => $hostel->name,
                'type' => $hostel->type,
                'type_text' => ucfirst(str_replace('_', ' ', $hostel->type)),
                'warden_name' => $hostel->warden_name,
                'warden_phone' => $hostel->warden_phone,
                'address' => $hostel->address,
            ],
            'available_rooms' => $rooms,
        ], 'Available rooms retrieved');
    }

    /**
     * Request hostel allocation (pending approval)
     */
    public function requestAllocation(Request $request)
    {
        $student = Auth::user()->student;

        $request->validate([
            'room_id' => 'required|exists:hostel_rooms,id',
        ]);

        // Check for existing active or pending allocation
        $existing = HostelAllocation::where('student_id', $student->id)
            ->whereIn('status', [HostelAllocation::STATUS_ACTIVE, HostelAllocation::STATUS_PENDING])
            ->first();

        if ($existing) {
            return $this->sendError(
                'You already have a ' . $existing->status . ' hostel allocation',
                ['current_status' => $existing->status],
                422
            );
        }

        $room = HostelRoom::find($request->room_id);

        if ($room->occupied >= $room->capacity) {
            return $this->sendError('This room is no longer available', [], 422);
        }

        // Create allocation request with pending status
        $allocation = HostelAllocation::create([
            'student_id' => $student->id,
            'hostel_room_id' => $request->room_id,
            'allocation_date' => now(),
            'status' => HostelAllocation::STATUS_PENDING,
        ]);

        // Increment occupied count
        $room->increment('occupied');

        // Notify admin (optional)
        // Notification::create(...);

        return $this->sendResponse([
            'request_id' => $allocation->id,
            'status' => $allocation->status,
            'message' => 'Hostel allocation request submitted successfully. Waiting for approval.',
        ], 'Request submitted');
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
            ->map(fn($hostel) => [
                'id' => $hostel->id,
                'name' => $hostel->name,
                'type' => $hostel->type,
                'type_text' => ucfirst(str_replace('_', ' ', $hostel->type)),
                'warden_name' => $hostel->warden_name,
                'warden_phone' => $hostel->warden_phone,
                'address' => $hostel->address,
                'total_rooms' => $hostel->total_rooms,
                'available_rooms' => $hostel->available_rooms ?? 0,
            ]);

        return $this->sendResponse($hostels, 'All hostels retrieved');
    }

    /**
     * Get detailed room information with current occupants
     */
    public function roomDetails(HostelRoom $room)
    {
        $room->load(['hostel', 'allocations' => function($q) {
            $q->where('status', HostelAllocation::STATUS_ACTIVE)->with('student.user');
        }]);

        $currentOccupants = $room->allocations->map(fn($allocation) => [
            'name' => $allocation->student->user->name,
            'admission_number' => $allocation->student->admission_number,
            'allocation_date' => $allocation->allocation_date->format('d M, Y'),
            'profile_photo' => $allocation->student->user->profile_photo_url,
        ]);

        return $this->sendResponse([
            'room' => [
                'id' => $room->id,
                'number' => $room->room_number,
                'type' => $room->room_type,
                'type_text' => ucfirst(str_replace('_', ' ', $room->room_type)),
                'capacity' => $room->capacity,
                'occupied' => $room->occupied,
                'available_seats' => $room->available_seats,
                'fee_per_month' => $room->fee_per_month,
                'fee_per_month_formatted' => '₹ ' . number_format($room->fee_per_month, 2),
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
            'current_occupants' => $currentOccupants,
        ], 'Room details retrieved');
    }

    /**
     * Get hostel amenities list
     */
    private function getHostelAmenities()
    {
        return [
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
    }
}