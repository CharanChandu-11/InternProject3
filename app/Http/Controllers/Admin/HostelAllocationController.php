<?php
// app/Http/Controllers/Admin/HostelAllocationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HostelAllocationController extends Controller
{
    /**
     * Display all allocations (active + history)
     */
    public function index()
    {
        $allocations = HostelAllocation::with(['student.user', 'room.hostel'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.hostel-allocations.index', compact('allocations'));
    }

    /**
     * Show pending allocation requests
     */
    public function pending()
    {
        $pendingAllocations = HostelAllocation::where('status', HostelAllocation::STATUS_PENDING)
            ->with(['student.user', 'room.hostel'])
            ->orderBy('created_at')
            ->paginate(20);
        return view('admin.hostel-allocations.pending', compact('pendingAllocations'));
    }

    /**
     * Show form for direct allocation
     */
    public function create(Request $request)
    {
        $students = Student::with('user')
            ->whereDoesntHave('hostelAllocation', function ($q) {
                $q->where('status', HostelAllocation::STATUS_ACTIVE);
            })->get();

        $rooms = HostelRoom::with('hostel')
            ->whereRaw('occupied < capacity')
            ->get()
            ->map(function ($room) {
                $room->available_seats = $room->capacity - $room->occupied;
                return $room;
            });

        $selectedRoom = null;
        if ($request->has('room_id')) {
            $selectedRoom = HostelRoom::find($request->room_id);
        }

        return view('admin.hostel-allocations.create', compact('students', 'rooms', 'selectedRoom'));
    }

    /**
     * Store direct allocation (active immediately)
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id',
            'allocation_date' => 'required|date',
        ]);

        $room = HostelRoom::find($request->hostel_room_id);
        if ($room->occupied >= $room->capacity) {
            return redirect()->back()->with('error', 'Room is full. Cannot allocate.')->withInput();
        }

        DB::beginTransaction();
        try {
            $allocation = HostelAllocation::create([
                'student_id' => $request->student_id,
                'hostel_room_id' => $request->hostel_room_id,
                'allocation_date' => $request->allocation_date,
                'status' => HostelAllocation::STATUS_ACTIVE,
            ]);
            $room->increment('occupied');

            // Create/update hostel fee
            $this->syncHostelFee($allocation->student, $room->fee_per_month);

            DB::commit();
            return redirect()->route('admin.hostel-allocations.index')->with('success', 'Room allocated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to allocate: ' . $e->getMessage());
        }
    }

    /**
     * Approve a pending request
     */
    public function approve(HostelAllocation $hostelAllocation)
    {
        if ($hostelAllocation->status !== HostelAllocation::STATUS_PENDING) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        $room = $hostelAllocation->room;
        if ($room->occupied >= $room->capacity) {
            return redirect()->back()->with('error', 'Room is now full. Cannot approve.');
        }

        DB::beginTransaction();
        try {
            $hostelAllocation->update([
                'status' => HostelAllocation::STATUS_ACTIVE,
                'allocation_date' => now(),
            ]);
            // Occupied already incremented when request was created, so no increment here.

            // Create/update hostel fee
            $this->syncHostelFee($hostelAllocation->student, $room->fee_per_month);

            DB::commit();
            return redirect()->route('admin.hostel-allocations.pending')->with('success', 'Allocation approved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending request
     */
    public function reject(HostelAllocation $hostelAllocation)
    {
        if ($hostelAllocation->status !== HostelAllocation::STATUS_PENDING) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $hostelAllocation->update(['status' => HostelAllocation::STATUS_REJECTED]);

            // Decrement occupied count (since it was incremented when requested)
            $hostelAllocation->room->decrement('occupied');

            // Remove hostel fee (set due to zero)
            $this->removeHostelFee($hostelAllocation->student);

            DB::commit();
            return redirect()->route('admin.hostel-allocations.pending')->with('success', 'Allocation rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject: ' . $e->getMessage());
        }
    }

    /**
     * Delete an allocation (only if inactive or rejected)
     */
    public function destroy(HostelAllocation $hostelAllocation)
    {
        // Load relationships needed for cleanup
        $hostelAllocation->load(['room', 'student']);

        DB::beginTransaction();
        try {
            if ($hostelAllocation->status == HostelAllocation::STATUS_ACTIVE) {
                // Free the room seat if room exists
                if ($hostelAllocation->room) {
                    $hostelAllocation->room->decrement('occupied');
                }
                // Remove hostel fee if student exists
                if ($hostelAllocation->student) {
                    $this->removeHostelFee($hostelAllocation->student);
                }
            }
            $hostelAllocation->delete();
            DB::commit();
            return redirect()->route('admin.hostel-allocations.index')->with('success', 'Allocation record deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            Log::error('Hostel allocation deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }

    /**
     * Sync hostel fee for a student
     */
    private function syncHostelFee($student, $monthlyFee)
    {
        $academicYear = AcademicYear::where('is_current', true)->first();
        if (!$academicYear) {
            throw new \Exception('No current academic year set.');
        }

        $hostelCategory = FeeCategory::firstOrCreate(
            ['code' => 'HOSTEL'],
            ['name' => 'Hostel Fee', 'description' => 'Monthly hostel accommodation fee']
        );

        // Find or create fee structure for this student's class
        $feeStructure = FeeStructure::firstOrCreate(
            [
                'class_id' => $student->class_id,
                'fee_category_id' => $hostelCategory->id,
            ],
            [
                'amount' => $monthlyFee,
                'frequency' => 'monthly',
                'is_optional' => false,
            ]
        );
        // Update amount in case fee changed
        $feeStructure->amount = $monthlyFee;
        $feeStructure->save();

        // Create or update student fee record
        StudentFee::updateOrCreate(
            [
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
            ],
            [
                'total_amount' => $monthlyFee,
                'paid_amount' => 0,
                'due_amount' => $monthlyFee,
                'due_date' => now()->endOfMonth(),
                'status' => 'pending',
            ]
        );
    }

    /**
     * Remove hostel fee (set due to zero)
     */
    private function removeHostelFee($student)
    {
        $hostelCategory = FeeCategory::where('code', 'HOSTEL')->first();
        if (!$hostelCategory) return;

        $feeStructure = FeeStructure::where('fee_category_id', $hostelCategory->id)
            ->whereHas('class', fn($q) => $q->where('id', $student->class_id))
            ->first();

        if ($feeStructure) {
            StudentFee::where('student_id', $student->id)
                ->where('fee_structure_id', $feeStructure->id)
                ->update([
                    'due_amount' => 0,
                    'status' => 'paid',
                ]);
        }
    }
}