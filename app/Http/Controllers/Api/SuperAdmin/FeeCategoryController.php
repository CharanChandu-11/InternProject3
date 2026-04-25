<?php
// app/Http/Controllers/Api/SuperAdmin/FeeCategoryController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\FeeCategory;
use Illuminate\Http\Request;

class FeeCategoryController extends BaseController
{
    public function index()
    {
        return $this->sendResponse(FeeCategory::all(), 'Fee categories retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|unique:fee_categories',
            'description' => 'nullable|string',
        ]);
        $category = FeeCategory::create($validated);
        return $this->sendResponse($category, 'Fee category created', 201);
    }

    public function show(FeeCategory $feeCategory)
    {
        return $this->sendResponse($feeCategory, 'Fee category retrieved');
    }

    public function update(Request $request, FeeCategory $feeCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'code' => 'sometimes|unique:fee_categories,code,' . $feeCategory->id,
            'description' => 'nullable|string',
        ]);
        $feeCategory->update($validated);
        return $this->sendResponse($feeCategory, 'Fee category updated');
    }

    public function destroy(FeeCategory $feeCategory)
    {
        $feeCategory->delete();
        return $this->sendResponse([], 'Fee category deleted');
    }
}