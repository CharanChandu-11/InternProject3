<?php
// app/Http/Controllers/Api/SectionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function getSectionsByClass($classId)
    {
        $class = Classes::with('sections')->findOrFail($classId);
        return response()->json($class->sections);
    }
}