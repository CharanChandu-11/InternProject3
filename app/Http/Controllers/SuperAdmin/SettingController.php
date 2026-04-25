<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $schoolSettings = SchoolSetting::first();
        $systemSettings = SystemSetting::pluck('value', 'key')->toArray();
        
        return view('super-admin.settings.index', compact('schoolSettings', 'systemSettings'));
    }
    
    public function updateSchool(Request $request)
    {
        $request->validate([
            'school_name' => 'required',
            'school_code' => 'required',
            'board' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'pincode' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'logo' => 'nullable|image|max:2048'
        ]);
        
        $school = SchoolSetting::first();
        
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('school', 'public');
            $request->merge(['logo' => $path]);
        }
        
        $school->update($request->all());
        
        return back()->with('success', 'School settings updated successfully.');
    }
    
    public function updateSystem(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $this->detectType($value)]
            );
        }
        
        return back()->with('success', 'System settings updated successfully.');
    }
    
    public function backup()
    {
        // Database backup
        $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
        
        // Execute backup command
        $command = 'mysqldump -u' . env('DB_USERNAME') . 
                   ' -p' . env('DB_PASSWORD') . 
                   ' ' . env('DB_DATABASE') . 
                   ' > ' . storage_path('app/backups/' . $filename);
        
        exec($command);
        
        return response()->download(storage_path('app/backups/' . $filename));
    }
    
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql'
        ]);
        
        // Restore from backup
        $file = $request->file('backup_file');
        $content = file_get_contents($file);
        
        DB::unprepared($content);
        
        return back()->with('success', 'Database restored successfully.');
    }
    
    private function detectType($value)
    {
        if (is_numeric($value)) {
            return 'number';
        } elseif (in_array($value, ['true', 'false'])) {
            return 'boolean';
        } elseif (is_array(json_decode($value, true))) {
            return 'json';
        } else {
            return 'text';
        }
    }
}