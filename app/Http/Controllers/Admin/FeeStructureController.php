<?php
// app/Http/Controllers/Admin/FeeStructureController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\Classes;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use App\Services\FeeService;

class FeeStructureController extends Controller
{
    public function index(Request $request)
    {
        $query = FeeStructure::with(['class', 'feeCategory']);
        
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('category_id')) {
            $query->where('fee_category_id', $request->category_id);
        }
        
        $feeStructures = $query->paginate(20);
        $classes = Classes::all();
        $categories = FeeCategory::all();
        
        return view('admin.fee-structures.index', compact('feeStructures', 'classes', 'categories'));
    }
    
    public function create()
    {
        $classes = Classes::all();
        $categories = FeeCategory::all();
        return view('admin.fee-structures.create', compact('classes', 'categories'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,half_yearly,yearly,one_time',
            'is_optional' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        
        $validated['is_optional'] = $request->has('is_optional');
        
        $feeStructure = FeeStructure::create($validated);
        FeeService::generateForNewFeeStructure($feeStructure);
        
        return redirect()->route('admin.fee-structures.index')
            ->with('success', 'Fee structure created successfully.');
    }
    
    public function edit(FeeStructure $feeStructure)
    {
        $classes = Classes::all();
        $categories = FeeCategory::all();
        return view('admin.fee-structures.edit', compact('feeStructure', 'classes', 'categories'));
    }
    
    public function update(Request $request, FeeStructure $feeStructure)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,half_yearly,yearly,one_time',
            'is_optional' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        
        $validated['is_optional'] = $request->has('is_optional');
        
        $feeStructure->update($validated);
        
        return redirect()->route('admin.fee-structures.index')
            ->with('success', 'Fee structure updated successfully.');
    }
    
    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();
        return redirect()->route('admin.fee-structures.index')
            ->with('success', 'Fee structure deleted successfully.');
    }
}