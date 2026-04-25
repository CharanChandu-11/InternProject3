<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Models\Student;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\AcademicYear;
use App\Models\StudentTransport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportRouteController extends Controller
{
    public function index()
    {
        $routes = TransportRoute::with('vehicles', 'stops')->orderBy('route_number')->paginate(20);
        return view('admin.transport-routes.index', compact('routes'));
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('admin.transport-routes.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'route_number' => 'required|string|max:50|unique:transport_routes',
            'route_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vehicles' => 'nullable|array',
            'vehicles.*' => 'exists:vehicles,id',
            'stops' => 'required|array|min:1',
            'stops.*.stop_name' => 'required|string',
            'stops.*.pickup_time' => 'required|date_format:H:i',
            'stops.*.drop_time' => 'required|date_format:H:i|after:stops.*.pickup_time',
            'stops.*.fee' => 'required|numeric|min:0',
            'stops.*.distance' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $route = TransportRoute::create($request->only(['route_number', 'route_name', 'description']));
            if ($request->has('vehicles')) {
                $route->vehicles()->sync($request->vehicles);
            }
            $order = 1;
            foreach ($request->stops as $stopData) {
                $route->stops()->create([
                    'stop_name' => $stopData['stop_name'],
                    'pickup_time' => $stopData['pickup_time'],
                    'drop_time' => $stopData['drop_time'],
                    'fee' => $stopData['fee'],
                    'distance_from_previous' => $stopData['distance'] ?? null,
                    'sort_order' => $order++,
                ]);
            }
            DB::commit();
            return redirect()->route('admin.transport-routes.index')->with('success', 'Route created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create route.')->withInput();
        }
    }

    public function show(TransportRoute $transportRoute)
    {
        $transportRoute->load('vehicles', 'stops');
        return view('admin.transport-routes.show', compact('transportRoute'));
    }

    public function edit(TransportRoute $transportRoute)
    {
        $vehicles = Vehicle::all();
        $transportRoute->load('vehicles', 'stops');
        return view('admin.transport-routes.edit', compact('transportRoute', 'vehicles'));
    }

    public function update(Request $request, TransportRoute $transportRoute)
    {
        $request->validate([
            'route_number' => 'required|string|max:50|unique:transport_routes,route_number,' . $transportRoute->id,
            'route_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vehicles' => 'nullable|array',
            'vehicles.*' => 'exists:vehicles,id',
            'stops' => 'required|array|min:1',
            'stops.*.stop_name' => 'required|string',
            'stops.*.pickup_time' => 'required|date_format:H:i',
            'stops.*.drop_time' => 'required|date_format:H:i|after:stops.*.pickup_time',
            'stops.*.fee' => 'required|numeric|min:0',
            'stops.*.distance' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $transportRoute->update($request->only(['route_number', 'route_name', 'description']));
            $transportRoute->vehicles()->sync($request->vehicles ?? []);
            // Remove existing stops and recreate
            $transportRoute->stops()->delete();
            $order = 1;
            foreach ($request->stops as $stopData) {
                $transportRoute->stops()->create([
                    'stop_name' => $stopData['stop_name'],
                    'pickup_time' => $stopData['pickup_time'],
                    'drop_time' => $stopData['drop_time'],
                    'fee' => $stopData['fee'],
                    'distance_from_previous' => $stopData['distance'] ?? null,
                    'sort_order' => $order++,
                ]);
            }
            DB::commit();
            return redirect()->route('admin.transport-routes.index')->with('success', 'Route updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update route.')->withInput();
        }
    }

    public function destroy(TransportRoute $transportRoute)
    {
        $transportRoute->delete();
        return redirect()->route('admin.transport-routes.index')->with('success', 'Route deleted.');
    }

    public function students(TransportRoute $transportRoute)
    {
        $students = $transportRoute->studentTransports()->with('student.user', 'stop')->paginate(20);
        return view('admin.transport-routes.students', compact('transportRoute', 'students'));
    }

    public function allocateForm(TransportRoute $transportRoute)
    {
        $students = Student::with('user')->orderBy('user_id')->get();
        $stops = $transportRoute->stops()->get();
        return view('admin.transport-routes.allocations.create', compact('transportRoute', 'students', 'stops'));
    }

    public function allocateStore(Request $request, TransportRoute $transportRoute)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'stop_id' => 'required|exists:stops,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $student = Student::find($request->student_id);
        $stop = $transportRoute->stops()->find($request->stop_id);
        if (!$stop) {
            return redirect()->back()->with('error', 'Invalid stop for this route.');
        }

        // Check existing active allocation
        $existing = StudentTransport::where('student_id', $request->student_id)
            ->where('is_active', true)
            ->first();
        if ($existing) {
            return redirect()->back()->with('error', 'This student already has an active transport allocation.')->withInput();
        }

        DB::beginTransaction();
        try {
            // Create transport allocation
            $allocation = StudentTransport::create([
                'student_id' => $request->student_id,
                'transport_route_id' => $transportRoute->id,
                'stop_id' => $request->stop_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => true,
            ]);

            // --- Create/Update Transport Fee ---
            $academicYear = AcademicYear::where('is_current', true)->first();
            if (!$academicYear) {
                throw new \Exception('No current academic year set.');
            }

            $transportCategory = FeeCategory::firstOrCreate(
                ['code' => 'TRANS'],
                ['name' => 'Transport Fee', 'description' => 'Monthly transport fee']
            );

            // Find or create fee structure for this student's class
            $feeStructure = FeeStructure::firstOrCreate(
                [
                    'class_id' => $student->class_id,
                    'fee_category_id' => $transportCategory->id,
                ],
                [
                    'amount' => $stop->fee,
                    'frequency' => 'monthly',
                    'is_optional' => false,
                ]
            );
            // Update amount from stop fee (in case stop fee changed)
            $feeStructure->amount = $stop->fee;
            $feeStructure->save();

            // Create or update student fee record
            StudentFee::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructure->id,
                ],
                [
                    'total_amount' => $stop->fee,
                    'paid_amount' => 0,
                    'due_amount' => $stop->fee,
                    'due_date' => now()->endOfMonth(),
                    'status' => 'pending',
                ]
            );

            DB::commit();
            return redirect()->route('admin.transport-routes.show', $transportRoute)
                ->with('success', 'Student allocated to route and transport fee generated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Allocation failed: ' . $e->getMessage())->withInput();
        }
    }

    public function allocateDestroy(StudentTransport $allocation)
    {
        DB::beginTransaction();
        try {
            // Remove the transport fee for this student
            $transportCategory = FeeCategory::where('code', 'TRANS')->first();
            if ($transportCategory) {
                $feeStructure = FeeStructure::where('fee_category_id', $transportCategory->id)
                    ->whereHas('class', function ($q) use ($allocation) {
                        $q->where('id', $allocation->student->class_id);
                    })->first();
                if ($feeStructure) {
                    StudentFee::where('student_id', $allocation->student_id)
                        ->where('fee_structure_id', $feeStructure->id)
                        ->update([
                            'due_amount' => 0,
                            'status' => 'paid',
                        ]);
                }
            }

            $allocation->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Allocation removed and transport fee cleared.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to remove allocation: ' . $e->getMessage());
        }
    }
}