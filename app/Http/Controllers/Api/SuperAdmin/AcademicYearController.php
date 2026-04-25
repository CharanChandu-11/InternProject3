<?php
// app/Http/Controllers/Api/SuperAdmin/AcademicYearController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends BaseController
{
    public function index()
    {
        $years = AcademicYear::orderBy('start_date', 'desc')->get();
        return $this->sendResponse($years, 'Academic years retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $year = AcademicYear::create($validated);
        return $this->sendResponse($year, 'Academic year created', 201);
    }

    public function show(AcademicYear $academicYear)
    {
        return $this->sendResponse($academicYear, 'Academic year retrieved');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_current' => 'boolean',
        ]);

        $academicYear->update($validated);
        return $this->sendResponse($academicYear, 'Academic year updated');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return $this->sendResponse([], 'Academic year deleted');
    }

    public function setCurrent(AcademicYear $academicYear)
    {
        AcademicYear::where('is_current', true)->update(['is_current' => false]);
        $academicYear->update(['is_current' => true]);
        return $this->sendResponse($academicYear, 'Current year set');
    }
}