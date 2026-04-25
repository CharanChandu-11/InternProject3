<?php
// app/Http/Controllers/Admin/SettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\SystemSetting;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    protected $settings;
    protected $systemSettings;

    public function __construct()
    {
        $this->settings = SchoolSetting::first();
        $this->systemSettings = SystemSetting::pluck('value', 'key')->toArray();
        view()->share('settings', $this->settings);
        view()->share('academicYears', AcademicYear::all());
        view()->share('academicSettings', $this->getAcademicSettings());
        view()->share('feeSettings', $this->getFeeSettings());
        view()->share('attendanceSettings', $this->getAttendanceSettings());
        view()->share('notificationSettings', $this->getNotificationSettings());
    }

    public function index()
    {
        return view('admin.settings.index');
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_code' => 'required|string|max:50',
            'board' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = SchoolSetting::first();
        
        if ($request->hasFile('logo')) {
            if ($settings->logo) {
                Storage::disk('public')->delete($settings->logo);
            }
            $path = $request->file('logo')->store('school', 'public');
            $request->merge(['logo' => $path]);
        }

        $settings->update($request->except('_token', '_method'));

        return redirect()->route('admin.settings.index')
            ->with('success', 'General settings updated successfully.');
    }

    public function updateAcademic(Request $request)
    {
        $request->validate([
            'current_academic_year_id' => 'required|exists:academic_years,id',
            'passing_percentage' => 'required|integer|min:0|max:100',
            'max_marks_per_subject' => 'required|integer|min:1',
            'grading_system' => 'required|in:percentage,cgpa,letter_grade',
            'grading_scale' => 'required|array',
        ]);

        // Update current academic year
        AcademicYear::where('is_current', true)->update(['is_current' => false]);
        AcademicYear::where('id', $request->current_academic_year_id)->update(['is_current' => true]);

        // Save academic settings
        SystemSetting::set('passing_percentage', $request->passing_percentage);
        SystemSetting::set('max_marks_per_subject', $request->max_marks_per_subject);
        SystemSetting::set('grading_system', $request->grading_system);
        SystemSetting::set('grading_scale', json_encode($request->grading_scale));

        return redirect()->route('admin.settings.index')
            ->with('success', 'Academic settings updated successfully.');
    }

    public function updateFees(Request $request)
    {
        $request->validate([
            'late_fee_per_day' => 'required|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0',
            'payment_reminder_days' => 'required|integer|min:1',
            'default_payment_method' => 'required|in:cash,card,bank_transfer,online',
        ]);

        SystemSetting::set('late_fee_per_day', $request->late_fee_per_day);
        SystemSetting::set('grace_period_days', $request->grace_period_days);
        SystemSetting::set('payment_reminder_days', $request->payment_reminder_days);
        SystemSetting::set('default_payment_method', $request->default_payment_method);
        SystemSetting::set('auto_generate_invoice', $request->has('auto_generate_invoice'));
        SystemSetting::set('send_payment_reminders', $request->has('send_payment_reminders'));
        SystemSetting::set('enable_online_payment', $request->has('enable_online_payment'));

        return redirect()->route('admin.settings.index')
            ->with('success', 'Fees settings updated successfully.');
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'school_start_time' => 'required',
            'school_end_time' => 'required',
            'late_threshold_minutes' => 'required|integer|min:0',
            'min_attendance_percentage' => 'required|integer|min:0|max:100',
        ]);

        SystemSetting::set('school_start_time', $request->school_start_time);
        SystemSetting::set('school_end_time', $request->school_end_time);
        SystemSetting::set('late_threshold_minutes', $request->late_threshold_minutes);
        SystemSetting::set('min_attendance_percentage', $request->min_attendance_percentage);
        SystemSetting::set('enable_biometric', $request->has('enable_biometric'));
        SystemSetting::set('auto_mark_absent', $request->has('auto_mark_absent'));
        SystemSetting::set('send_attendance_alert', $request->has('send_attendance_alert'));

        return redirect()->route('admin.settings.index')
            ->with('success', 'Attendance settings updated successfully.');
    }

    public function updateNotification(Request $request)
    {
        SystemSetting::set('enable_email_notifications', $request->has('enable_email_notifications'));
        SystemSetting::set('send_email_on_attendance', $request->has('send_email_on_attendance'));
        SystemSetting::set('send_email_on_fee_payment', $request->has('send_email_on_fee_payment'));
        SystemSetting::set('enable_sms_notifications', $request->has('enable_sms_notifications'));
        SystemSetting::set('send_sms_on_attendance', $request->has('send_sms_on_attendance'));
        SystemSetting::set('send_sms_on_result', $request->has('send_sms_on_result'));
        SystemSetting::set('enable_push_notifications', $request->has('enable_push_notifications'));

        return redirect()->route('admin.settings.index')
            ->with('success', 'Notification settings updated successfully.');
    }

    public function backup(Request $request)
    {
        try {
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $backupPath . '/' . $filename;
            
            // Get database configuration
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            
            // Create backup command
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbName),
                escapeshellarg($filePath)
            );
            
            // Execute command
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Backup failed. Please check database credentials.');
            }
            
            // Log backup creation
            \Log::info('Database backup created: ' . $filename);
            
            return response()->download($filePath, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,zip|max:51200',
        ]);

        try {
            $file = $request->file('backup_file');
            $content = file_get_contents($file->getRealPath());
            
            // Split SQL file into individual queries
            $queries = explode(';', $content);
            
            DB::beginTransaction();
            try {
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        DB::unprepared($query);
                    }
                }
                DB::commit();
                
                \Log::info('Database restored from backup: ' . $file->getClientOriginalName());
                
                return redirect()->back()->with('success', 'Database restored successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function backupList()
    {
        $backupPath = storage_path('app/backups');
        $backups = [];
        
        if (file_exists($backupPath)) {
            $files = glob($backupPath . '/*.sql');
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'size' => filesize($file),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'path' => $file
                ];
            }
            $backups = array_reverse($backups);
        }
        
        if (request()->ajax()) {
            return response()->json($backups);
        }
        
        return view('admin.settings.partials.backup-list', compact('backups'));
    }

    public function deleteBackup($filename)
    {
        $filePath = storage_path('app/backups/' . $filename);
        
        if (file_exists($filePath)) {
            unlink($filePath);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'File not found'], 404);
    }

    public function clearCache(Request $request)
    {
        $type = $request->type ?? 'all';
        
        try {
            if ($type == 'all' || $type == 'config') {
                Artisan::call('config:clear');
                Artisan::call('config:cache');
            }
            
            if ($type == 'all' || $type == 'route') {
                Artisan::call('route:clear');
                Artisan::call('route:cache');
            }
            
            if ($type == 'all' || $type == 'view') {
                Artisan::call('view:clear');
                Artisan::call('view:cache');
            }
            
            if ($type == 'all') {
                Artisan::call('cache:clear');
                Artisan::call('optimize:clear');
            }
            
            return redirect()->back()->with('success', 'Cache cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function systemInfo()
    {
        $systemInfo = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'database_type' => DB::connection()->getDriverName(),
            'database_name' => DB::connection()->getDatabaseName(),
            'total_users' => \App\Models\User::count(),
            'total_students' => \App\Models\Student::count(),
            'total_teachers' => \App\Models\User::where('user_type', 'teacher')->count(),
            'total_parents' => \App\Models\User::where('user_type', 'parent')->count(),
            'total_classes' => \App\Models\Classes::count(),
            'total_subjects' => \App\Models\Subject::count(),
            'total_exams' => \App\Models\Exam::count(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
        
        return response()->json($systemInfo);
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);
        
        try {
            Mail::raw('This is a test email from your school management system.', function($message) use ($request) {
                $message->to($request->test_email)
                        ->subject('Test Email from School Management System');
            });
            
            return redirect()->back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function emailSettings()
    {
        $emailSettings = [
            'mail_driver' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];
        
        return view('admin.settings.email', compact('emailSettings'));
    }

    public function updateEmailSettings(Request $request)
    {
        $request->validate([
            'mail_driver' => 'required|in:smtp,sendmail,mailgun,ses,postmark,log',
            'mail_host' => 'required_if:mail_driver,smtp|nullable|string',
            'mail_port' => 'required_if:mail_driver,smtp|nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);
        
        // Update .env file
        $this->updateEnvFile([
            'MAIL_MAILER' => $request->mail_driver,
            'MAIL_HOST' => $request->mail_host,
            'MAIL_PORT' => $request->mail_port,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_PASSWORD' => $request->mail_password,
            'MAIL_ENCRYPTION' => $request->mail_encryption,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME' => '"' . $request->mail_from_name . '"',
        ]);
        
        // Clear config cache
        Artisan::call('config:clear');
        
        return redirect()->route('admin.settings.email')
            ->with('success', 'Email settings updated successfully.');
    }

    public function smsSettings()
    {
        $smsSettings = [
            'twilio_sid' => env('TWILIO_SID'),
            'twilio_token' => env('TWILIO_TOKEN'),
            'twilio_from' => env('TWILIO_FROM'),
            'sms_enabled' => env('SMS_ENABLED', false),
        ];
        
        return view('admin.settings.sms', compact('smsSettings'));
    }

    public function updateSmsSettings(Request $request)
    {
        $request->validate([
            'twilio_sid' => 'required_if:sms_enabled,on|nullable|string',
            'twilio_token' => 'required_if:sms_enabled,on|nullable|string',
            'twilio_from' => 'required_if:sms_enabled,on|nullable|string',
        ]);
        
        $smsEnabled = $request->has('sms_enabled');
        
        $this->updateEnvFile([
            'TWILIO_SID' => $request->twilio_sid,
            'TWILIO_TOKEN' => $request->twilio_token,
            'TWILIO_FROM' => $request->twilio_from,
            'SMS_ENABLED' => $smsEnabled ? 'true' : 'false',
        ]);
        
        return redirect()->route('admin.settings.sms')
            ->with('success', 'SMS settings updated successfully.');
    }

    public function paymentSettings()
    {
        $paymentSettings = [
            'stripe_key' => env('STRIPE_KEY'),
            'stripe_secret' => env('STRIPE_SECRET'),
            'razorpay_key' => env('RAZORPAY_KEY'),
            'razorpay_secret' => env('RAZORPAY_SECRET'),
            'payment_gateway' => env('PAYMENT_GATEWAY', 'stripe'),
        ];
        
        return view('admin.settings.payment', compact('paymentSettings'));
    }

    public function updatePaymentSettings(Request $request)
    {
        $request->validate([
            'payment_gateway' => 'required|in:stripe,razorpay,paypal',
            'stripe_key' => 'required_if:payment_gateway,stripe|nullable|string',
            'stripe_secret' => 'required_if:payment_gateway,stripe|nullable|string',
            'razorpay_key' => 'required_if:payment_gateway,razorpay|nullable|string',
            'razorpay_secret' => 'required_if:payment_gateway,razorpay|nullable|string',
        ]);
        
        $this->updateEnvFile([
            'PAYMENT_GATEWAY' => $request->payment_gateway,
            'STRIPE_KEY' => $request->stripe_key,
            'STRIPE_SECRET' => $request->stripe_secret,
            'RAZORPAY_KEY' => $request->razorpay_key,
            'RAZORPAY_SECRET' => $request->razorpay_secret,
        ]);
        
        return redirect()->route('admin.settings.payment')
            ->with('success', 'Payment settings updated successfully.');
    }

    public function securitySettings()
    {
        $securitySettings = [
            'login_attempts' => env('LOGIN_ATTEMPTS', 5),
            'lockout_time' => env('LOCKOUT_TIME', 15),
            'session_lifetime' => env('SESSION_LIFETIME', 120),
            'password_expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90),
            'two_factor_auth' => env('TWO_FACTOR_AUTH', false),
        ];
        
        return view('admin.settings.security', compact('securitySettings'));
    }

    public function updateSecuritySettings(Request $request)
    {
        $request->validate([
            'login_attempts' => 'required|integer|min:1|max:10',
            'lockout_time' => 'required|integer|min:1|max:60',
            'session_lifetime' => 'required|integer|min:15|max:1440',
            'password_expiry_days' => 'required|integer|min:0|max:365',
        ]);
        
        $this->updateEnvFile([
            'LOGIN_ATTEMPTS' => $request->login_attempts,
            'LOCKOUT_TIME' => $request->lockout_time,
            'SESSION_LIFETIME' => $request->session_lifetime,
            'PASSWORD_EXPIRY_DAYS' => $request->password_expiry_days,
            'TWO_FACTOR_AUTH' => $request->has('two_factor_auth') ? 'true' : 'false',
        ]);
        
        return redirect()->route('admin.settings.security')
            ->with('success', 'Security settings updated successfully.');
    }

    public function environment()
    {
        return view('admin.settings.environment');
    }

    public function updateEnvironment(Request $request)
    {
        $request->validate([
            'app_env' => 'required|in:local,production,staging',
            'app_debug' => 'required|in:true,false',
            'app_url' => 'required|url',
        ]);
        
        $this->updateEnvFile([
            'APP_ENV' => $request->app_env,
            'APP_DEBUG' => $request->app_debug,
            'APP_URL' => $request->app_url,
        ]);
        
        Artisan::call('config:clear');
        
        return redirect()->route('admin.settings.environment')
            ->with('success', 'Environment settings updated successfully.');
    }

    public function sessionSettings()
    {
        $sessionSettings = [
            'session_driver' => config('session.driver'),
            'session_lifetime' => config('session.lifetime'),
            'session_secure' => config('session.secure', false),
            'session_domain' => config('session.domain'),
        ];
        
        return view('admin.settings.session', compact('sessionSettings'));
    }

    public function updateSessionSettings(Request $request)
    {
        $request->validate([
            'session_driver' => 'required|in:file,cookie,database,redis,array',
            'session_lifetime' => 'required|integer|min:15|max:1440',
            'session_domain' => 'nullable|string',
        ]);
        
        $this->updateEnvFile([
            'SESSION_DRIVER' => $request->session_driver,
            'SESSION_LIFETIME' => $request->session_lifetime,
            'SESSION_DOMAIN' => $request->session_domain,
            'SESSION_SECURE' => $request->has('session_secure') ? 'true' : 'false',
        ]);
        
        Artisan::call('config:clear');
        
        return redirect()->route('admin.settings.session')
            ->with('success', 'Session settings updated successfully.');
    }

    public function maintenanceMode(Request $request)
    {
        if ($request->has('enable')) {
            Artisan::call('down', ['--secret' => $request->secret ?? null]);
            return redirect()->back()->with('success', 'Maintenance mode enabled.');
        } else {
            Artisan::call('up');
            return redirect()->back()->with('success', 'Maintenance mode disabled.');
        }
    }

    private function updateEnvFile($data)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }
        
        file_put_contents($envPath, $envContent);
    }

    private function getAcademicSettings()
    {
        return [
            'passing_percentage' => SystemSetting::get('passing_percentage', 40),
            'max_marks_per_subject' => SystemSetting::get('max_marks_per_subject', 100),
            'grading_system' => SystemSetting::get('grading_system', 'percentage'),
            'grading_scale' => json_decode(SystemSetting::get('grading_scale', '[]'), true),
        ];
    }

    private function getFeeSettings()
    {
        return [
            'late_fee_per_day' => SystemSetting::get('late_fee_per_day', 10),
            'grace_period_days' => SystemSetting::get('grace_period_days', 7),
            'payment_reminder_days' => SystemSetting::get('payment_reminder_days', 5),
            'default_payment_method' => SystemSetting::get('default_payment_method', 'cash'),
            'auto_generate_invoice' => SystemSetting::get('auto_generate_invoice', true),
            'send_payment_reminders' => SystemSetting::get('send_payment_reminders', true),
            'enable_online_payment' => SystemSetting::get('enable_online_payment', true),
        ];
    }

    private function getAttendanceSettings()
    {
        return [
            'school_start_time' => SystemSetting::get('school_start_time', '08:00:00'),
            'school_end_time' => SystemSetting::get('school_end_time', '15:00:00'),
            'late_threshold_minutes' => SystemSetting::get('late_threshold_minutes', 15),
            'min_attendance_percentage' => SystemSetting::get('min_attendance_percentage', 75),
            'enable_biometric' => SystemSetting::get('enable_biometric', false),
            'auto_mark_absent' => SystemSetting::get('auto_mark_absent', true),
            'send_attendance_alert' => SystemSetting::get('send_attendance_alert', true),
        ];
    }

    private function getNotificationSettings()
    {
        return [
            'enable_email_notifications' => SystemSetting::get('enable_email_notifications', true),
            'send_email_on_attendance' => SystemSetting::get('send_email_on_attendance', true),
            'send_email_on_fee_payment' => SystemSetting::get('send_email_on_fee_payment', true),
            'enable_sms_notifications' => SystemSetting::get('enable_sms_notifications', false),
            'send_sms_on_attendance' => SystemSetting::get('send_sms_on_attendance', true),
            'send_sms_on_result' => SystemSetting::get('send_sms_on_result', true),
            'enable_push_notifications' => SystemSetting::get('enable_push_notifications', true),
        ];
    }
}