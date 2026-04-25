<?php
// app/Http/Controllers/Api/SuperAdmin/FeeStructureController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\FeeStructure;
use Illuminate\Http\Request;

class FeeStructureController extends BaseController
{
    public function index()
    {
        $structures = FeeStructure::with(['class', 'feeCategory'])->get();
        return $this->sendResponse($structures, 'Fee structures retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,half_yearly,yearly,one_time',
            'is_optional' => 'boolean',
        ]);
        $structure = FeeStructure::create($validated);
        return $this->sendResponse($structure, 'Fee structure created', 201);
    }

    public function show(FeeStructure $feeStructure)
    {
        $feeStructure->load(['class', 'feeCategory']);
        return $this->sendResponse($feeStructure, 'Fee structure retrieved');
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $validated = $request->validate([
            'class_id' => 'sometimes|exists:classes,id',
            'fee_category_id' => 'sometimes|exists:fee_categories,id',
            'amount' => 'sometimes|numeric|min:0',
            'frequency' => 'sometimes|in:monthly,quarterly,half_yearly,yearly,one_time',
            'is_optional' => 'boolean',
        ]);
        $feeStructure->update($validated);
        return $this->sendResponse($feeStructure, 'Fee structure updated');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();
        return $this->sendResponse([], 'Fee structure deleted');
    }
}