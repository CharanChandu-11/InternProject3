<?php
// app/Http/Controllers/SuperAdmin/AcademicYearController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years
     */
    public function index(Request $request)
    {
        $query = AcademicYear::query();
        
        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        
        $academicYears = $query->orderBy('start_date', 'desc')->paginate(20);
        
        return view('super-admin.academic-years.index', compact('academicYears'));
    }
    
    /**
     * Show form for creating new academic year
     */
    public function create()
    {
        return view('super-admin.academic-years.create');
    }
    
    /**
     * Store a newly created academic year
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:academic_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
        ]);
        
        DB::beginTransaction();
        
        try {
            // If setting as current, unset other current years
            if ($request->is_current) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }
            
            $academicYear = AcademicYear::create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_current' => $request->is_current ?? false,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'module' => 'academic_year',
                'description' => "Created academic year: {$academicYear->name}",
            ]);
            
            return redirect()->route('super-admin.academic-years.index')
                ->with('success', 'Academic year created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create academic year: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display academic year details
     */
    public function show(AcademicYear $academicYear)
    {
        // Load related data
        $classes = $academicYear->classes()->withCount('students')->get();
        $studentsCount = $academicYear->students()->count();
        $examsCount = $academicYear->exams()->count();
        
        return view('super-admin.academic-years.show', compact('academicYear', 'classes', 'studentsCount', 'examsCount'));
    }
    
    /**
     * Show form for editing academic year
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('super-admin.academic-years.edit', compact('academicYear'));
    }
    
    /**
     * Update the specified academic year
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
        ]);
        
        DB::beginTransaction();
        
        try {
            // If setting as current, unset other current years
            if ($request->is_current && !$academicYear->is_current) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }
            
            $academicYear->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_current' => $request->is_current ?? false,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'module' => 'academic_year',
                'description' => "Updated academic year: {$academicYear->name}",
            ]);
            
            return redirect()->route('super-admin.academic-years.index')
                ->with('success', 'Academic year updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update academic year: ' . $e->getMessage());
        }
    }
    
    /**
     * Set academic year as current
     */
    public function setCurrent(AcademicYear $academicYear)
    {
        DB::beginTransaction();
        
        try {
            // Unset all current years
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
            
            // Set this as current
            $academicYear->update(['is_current' => true]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'set_current',
                'module' => 'academic_year',
                'description' => "Set academic year {$academicYear->name} as current",
            ]);
            
            return redirect()->route('super-admin.academic-years.index')
                ->with('success', "{$academicYear->name} set as current academic year.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to set current academic year: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete academic year
     */
    public function destroy(AcademicYear $academicYear)
    {
        // Check if has related data
        if ($academicYear->classes()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete academic year with associated classes.');
        }
        
        if ($academicYear->students()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete academic year with associated students.');
        }
        
        if ($academicYear->exams()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete academic year with associated exams.');
        }
        
        $yearName = $academicYear->name;
        $academicYear->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'module' => 'academic_year',
            'description' => "Deleted academic year: {$yearName}",
        ]);
        
        return redirect()->route('super-admin.academic-years.index')
            ->with('success', 'Academic year deleted successfully.');
    }
    
    /**
     * Clone academic year (create next year from current)
     */
    public function clone(AcademicYear $academicYear)
    {
        DB::beginTransaction();
        
        try {
            $newYear = AcademicYear::create([
                'name' => 'AY ' . ($academicYear->start_date->year + 1) . '-' . ($academicYear->end_date->year + 1),
                'start_date' => $academicYear->start_date->copy()->addYear(),
                'end_date' => $academicYear->end_date->copy()->addYear(),
                'is_current' => false,
            ]);
            
            DB::commit();
            
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'cloned',
                'module' => 'academic_year',
                'description' => "Cloned academic year: {$academicYear->name} to {$newYear->name}",
            ]);
            
            return redirect()->route('super-admin.academic-years.index')
                ->with('success', "Academic year cloned successfully. New year: {$newYear->name}");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to clone academic year: ' . $e->getMessage());
        }
    }
}