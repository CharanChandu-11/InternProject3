<?php
// app/Http/Controllers/Api/SuperAdmin/SettingController.php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function index()
    {
        $settings = SchoolSetting::first();
        return $this->sendResponse($settings, 'Settings retrieved');
    }

    public function update(Request $request)
    {
        $settings = SchoolSetting::first();
        $validated = $request->validate([
            'school_name' => 'sometimes|string',
            'school_code' => 'sometimes|string',
            'board' => 'sometimes|string',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'state' => 'sometimes|string',
            'pincode' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'email' => 'sometimes|email',
            'website' => 'nullable|url',
            'logo' => 'nullable|image',
        ]);
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('school', 'public');
            $validated['logo'] = $path;
        }
        $settings->update($validated);
        return $this->sendResponse($settings, 'Settings updated');
    }

    public function backup()
    {
        // Generate backup file (dummy)
        $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        // ... backup logic
        return $this->sendResponse(['file' => $backupFile], 'Backup created');
    }

    public function restore(Request $request)
    {
        $request->validate(['backup_file' => 'required|file']);
        // ... restore logic
        return $this->sendResponse([], 'Database restored');
    }
}