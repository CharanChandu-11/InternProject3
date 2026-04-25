<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC WEBSITE ROUTES ====================
Route::name('website.')->group(function () {
    // Homepage
    Route::get('/', [WebsiteController::class, 'index'])->name('home');
    
    // Static Pages
    Route::get('/about-us', [WebsiteController::class, 'aboutUs'])->name('about');
    Route::get('/admissions', [WebsiteController::class, 'admissions'])->name('admissions');
    Route::get('/academics', [WebsiteController::class, 'academics'])->name('academics');
    Route::get('/faculty', [WebsiteController::class, 'faculty'])->name('faculty');
    Route::get('/gallery', [WebsiteController::class, 'gallery'])->name('gallery');
    Route::get('/events', [WebsiteController::class, 'events'])->name('events');
    Route::get('/news', [WebsiteController::class, 'news'])->name('news');
    Route::get('/contact-us', [WebsiteController::class, 'contact'])->name('contact');
    
    // Contact Form Submission
    Route::post('/contact-submit', [WebsiteController::class, 'submitContact'])->name('contact.submit');
    Route::post('/newsletter-subscribe', [WebsiteController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');
    Route::post('/admission-inquiry', [WebsiteController::class, 'admissionInquiry'])->name('admission.inquiry');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// ==================== AUTHENTICATION ROUTES ====================
Route::prefix('auth')->name('auth.')->group(function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Registration Routes (if enabled)
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Password Reset Routes
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
    
    // Email Verification Routes
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

// ==================== PROTECTED ROUTES ====================
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Redirect based on user type
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        switch ($user->user_type) {
            case 'super_admin':
                return redirect()->route('super-admin.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            case 'parent':
                return redirect()->route('parent.dashboard');
            case 'employee':
                return redirect()->route('employee.dashboard');
            default:
                return redirect()->route('website.home');
        }
    })->name('dashboard');
    
    // Profile Routes (Common for all users)
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.change-password');
    
    // Notifications Routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    
    // Messages Routes
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [App\Http\Controllers\MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages/send', [App\Http\Controllers\MessageController::class, 'send'])->name('messages.send');
    
    // ==================== SUPER ADMIN ROUTES ====================
    Route::prefix('super-admin')->name('super-admin.')->middleware(['user.type:super_admin'])->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
        
        // User Management
        Route::resource('users', App\Http\Controllers\SuperAdmin\UserController::class);
        Route::post('users/{user}/toggle-status', [App\Http\Controllers\SuperAdmin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('users/import', [App\Http\Controllers\SuperAdmin\UserController::class, 'importForm'])->name('users.import-form');
        Route::post('users/import', [App\Http\Controllers\SuperAdmin\UserController::class, 'import'])->name('users.import');
        Route::get('users/export', [App\Http\Controllers\SuperAdmin\UserController::class, 'export'])->name('users.export');
        
        // Student Management
        Route::resource('students', App\Http\Controllers\SuperAdmin\StudentController::class);
        Route::post('students/{student}/promote', [App\Http\Controllers\SuperAdmin\StudentController::class, 'promote'])->name('students.promote');
        Route::get('students/{student}/id-card', [App\Http\Controllers\SuperAdmin\StudentController::class, 'generateIdCard'])->name('students.id-card');
        Route::get('students/import', [App\Http\Controllers\SuperAdmin\StudentController::class, 'importForm'])->name('students.import-form');
        Route::post('students/import', [App\Http\Controllers\SuperAdmin\StudentController::class, 'import'])->name('students.import');
        Route::get('students/export', [App\Http\Controllers\SuperAdmin\StudentController::class, 'export'])->name('students.export');
        
        // Teacher Management
        Route::resource('teachers', App\Http\Controllers\SuperAdmin\TeacherController::class);
        Route::post('teachers/{teacher}/assign-class', [App\Http\Controllers\SuperAdmin\TeacherController::class, 'assignClass'])->name('teachers.assign-class');
        Route::get('teachers/import', [App\Http\Controllers\SuperAdmin\TeacherController::class, 'importForm'])->name('teachers.import-form');
        Route::post('teachers/import', [App\Http\Controllers\SuperAdmin\TeacherController::class, 'import'])->name('teachers.import');
        
        // Employee Management
        Route::resource('employees', App\Http\Controllers\SuperAdmin\EmployeeController::class);
        Route::get('employees/import', [App\Http\Controllers\SuperAdmin\EmployeeController::class, 'importForm'])->name('employees.import-form');
        Route::post('employees/import', [App\Http\Controllers\SuperAdmin\EmployeeController::class, 'import'])->name('employees.import');
        Route::post('employees/{employee}/toggle-status', [App\Http\Controllers\SuperAdmin\EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
        Route::get('employees/export', [App\Http\Controllers\SuperAdmin\EmployeeController::class, 'export'])->name('employees.export');
        
        // Parent Management
        Route::resource('parents', App\Http\Controllers\SuperAdmin\ParentController::class);
        Route::get('parents/import', [App\Http\Controllers\SuperAdmin\ParentController::class, 'importForm'])->name('parents.import-form');
        Route::post('parents/import', [App\Http\Controllers\SuperAdmin\ParentController::class, 'import'])->name('parents.import');
        Route::post('parents/{parent}/toggle-status', [App\Http\Controllers\SuperAdmin\ParentController::class, 'toggleStatus'])->name('parents.toggle-status');
        Route::get('parents/export', [App\Http\Controllers\SuperAdmin\ParentController::class, 'export'])->name('parents.export');
        
        // Academic Management
        Route::resource('academic-years', App\Http\Controllers\SuperAdmin\AcademicYearController::class);
        Route::post('academic-years/{academicYear}/set-current', [App\Http\Controllers\SuperAdmin\AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
        Route::post('academic-years/{academicYear}/clone', [App\Http\Controllers\SuperAdmin\AcademicYearController::class, 'clone'])->name('academic-years.clone');
        
        Route::resource('classes', App\Http\Controllers\SuperAdmin\ClassController::class);
        Route::get('classes/{class}/students', [App\Http\Controllers\SuperAdmin\ClassController::class, 'students'])->name('classes.students');
        Route::post('classes/{class}/assign-teacher', [App\Http\Controllers\SuperAdmin\ClassController::class, 'assignTeacher'])->name('classes.assign-teacher');
        Route::post('classes/{class}/add-section', [App\Http\Controllers\SuperAdmin\ClassController::class, 'addSection'])->name('classes.add-section');
        Route::put('sections/{section}', [App\Http\Controllers\SuperAdmin\ClassController::class, 'editSection'])->name('classes.edit-section');
        Route::delete('sections/{section}', [App\Http\Controllers\SuperAdmin\ClassController::class, 'deleteSection'])->name('classes.delete-section');

        Route::resource('sections', App\Http\Controllers\SuperAdmin\SectionController::class);
        Route::get('sections/by-class/{classId}', [App\Http\Controllers\SuperAdmin\SectionController::class, 'getByClass'])->name('sections.by-class');
        Route::post('sections/bulk-create', [App\Http\Controllers\SuperAdmin\SectionController::class, 'bulkCreate'])->name('sections.bulk-create');
        Route::get('classes/by-academic-year/{academicYearId}', function($academicYearId) {
            return \App\Models\Classes::where('academic_year_id', $academicYearId)->get(['id', 'name']);
        })->name('classes.by-academic-year');

        Route::resource('subjects', App\Http\Controllers\SuperAdmin\SubjectController::class);
        Route::post('subjects/{subject}/assign-to-class', [App\Http\Controllers\SuperAdmin\SubjectController::class, 'assignToClass'])->name('subjects.assign-to-class');
        Route::delete('subjects/remove-from-class/{assignment}', [App\Http\Controllers\SuperAdmin\SubjectController::class, 'removeFromClass'])->name('subjects.remove-from-class');
        Route::put('subjects/update-assignment/{assignment}', [App\Http\Controllers\SuperAdmin\SubjectController::class, 'updateAssignment'])->name('subjects.update-assignment');
        Route::get('subjects/by-class/{classId}', [App\Http\Controllers\SuperAdmin\SubjectController::class, 'getByClass'])->name('subjects.by-class');
        Route::post('subjects/bulk-import', [App\Http\Controllers\SuperAdmin\SubjectController::class, 'bulkImport'])->name('subjects.bulk-import');
        
        Route::resource('timetable', App\Http\Controllers\SuperAdmin\TimetableController::class);
        Route::get('timetable/class/{class}/section/{section}/edit-grid', [App\Http\Controllers\SuperAdmin\TimetableController::class, 'editGrid'])->name('timetable.edit-grid');
        Route::post('timetable/class/{class}/section/{section}/update-grid', [App\Http\Controllers\SuperAdmin\TimetableController::class, 'updateGrid'])->name('timetable.update-grid');
        Route::get('timetable/class/{class}/section/{section}/export', [App\Http\Controllers\SuperAdmin\TimetableController::class, 'export'])->name('timetable.export');
        Route::get('/get-sections/{class}', function($classId) { 
            $class = \App\Models\Classes::find($classId);
            return response()->json($class ? $class->sections : []);
        })->name('get-sections');
        
        // Attendance Management
        Route::resource('attendance', App\Http\Controllers\SuperAdmin\AttendanceController::class);
        Route::get('attendance/summary', [App\Http\Controllers\SuperAdmin\AttendanceController::class, 'summary'])->name('attendance.summary');
        Route::get('attendance/class/{class}/section/{section}', [App\Http\Controllers\SuperAdmin\AttendanceController::class, 'mark'])->name('attendance.mark');
        Route::post('attendance/class/{class}/section/{section}', [App\Http\Controllers\SuperAdmin\AttendanceController::class, 'store'])->name('attendance.store');
        
        // Examination Management
        Route::resource('exams', App\Http\Controllers\SuperAdmin\ExamController::class);
        Route::resource('exam-schedules', App\Http\Controllers\SuperAdmin\ExamScheduleController::class);
        Route::resource('exam-results', App\Http\Controllers\SuperAdmin\ExamResultController::class);
        Route::get('exams/{exam}/results', [App\Http\Controllers\SuperAdmin\ExamController::class, 'results'])->name('exams.results');
        Route::post('exams/{exam}/publish-results', [App\Http\Controllers\SuperAdmin\ExamController::class, 'publishResults'])->name('exams.publish-results');
        
        // Fees Management
        Route::resource('fee-categories', App\Http\Controllers\SuperAdmin\FeeCategoryController::class);
        Route::resource('fee-structures', App\Http\Controllers\SuperAdmin\FeeStructureController::class);
        Route::resource('student-fees', App\Http\Controllers\SuperAdmin\StudentFeeController::class);
        Route::resource('payments', App\Http\Controllers\SuperAdmin\PaymentController::class);
        Route::get('payments/{payment}/receipt', [App\Http\Controllers\SuperAdmin\PaymentController::class, 'receipt'])->name('payments.receipt');
        
        // Library Management
        Route::resource('books', App\Http\Controllers\SuperAdmin\BookController::class);
        Route::resource('book-issues', App\Http\Controllers\SuperAdmin\BookIssueController::class);
        Route::post('books/{book}/return', [App\Http\Controllers\SuperAdmin\BookController::class, 'returnBook'])->name('books.return');
        Route::get('books/export', [App\Http\Controllers\SuperAdmin\BookController::class, 'export'])->name('books.export');
        
        // Transport Management
        Route::resource('transport-routes', App\Http\Controllers\SuperAdmin\TransportRouteController::class);
        Route::resource('vehicles', App\Http\Controllers\SuperAdmin\VehicleController::class);
        Route::resource('stops', App\Http\Controllers\SuperAdmin\StopController::class);
        Route::get('transport-routes/{route}/students', [App\Http\Controllers\SuperAdmin\TransportRouteController::class, 'students'])->name('transport-routes.students');
        
        // Hostel Management
        Route::resource('hostels', App\Http\Controllers\SuperAdmin\HostelController::class);
        Route::resource('hostel-rooms', App\Http\Controllers\SuperAdmin\HostelRoomController::class);
        Route::resource('hostel-allocations', App\Http\Controllers\SuperAdmin\HostelAllocationController::class);
        Route::get('hostels/{hostel}/rooms', [App\Http\Controllers\SuperAdmin\HostelController::class, 'rooms'])->name('hostels.rooms');
        
        // Communication
        Route::resource('announcements', App\Http\Controllers\SuperAdmin\AnnouncementController::class);
        Route::resource('events', App\Http\Controllers\SuperAdmin\EventController::class);
        Route::resource('gallery', App\Http\Controllers\SuperAdmin\GalleryController::class);
        Route::post('gallery/{gallery}/toggle-featured', [App\Http\Controllers\SuperAdmin\GalleryController::class, 'toggleFeatured'])->name('gallery.toggle-featured');
        Route::post('gallery/update-order', [App\Http\Controllers\SuperAdmin\GalleryController::class, 'updateOrder'])->name('gallery.update-order');
        Route::post('gallery/bulk-delete', [App\Http\Controllers\SuperAdmin\GalleryController::class, 'bulkDestroy'])->name('gallery.bulk-delete');
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('student-performance', [App\Http\Controllers\SuperAdmin\ReportController::class, 'studentPerformance'])->name('student-performance');
            Route::get('teacher-performance', [App\Http\Controllers\SuperAdmin\ReportController::class, 'teacherPerformance'])->name('teacher-performance');
            Route::get('attendance', [App\Http\Controllers\SuperAdmin\ReportController::class, 'attendance'])->name('attendance');
            Route::get('fees', [App\Http\Controllers\SuperAdmin\ReportController::class, 'fees'])->name('fees');
            Route::get('exam-results', [App\Http\Controllers\SuperAdmin\ReportController::class, 'examResults'])->name('exam-results');
            Route::get('financial', [App\Http\Controllers\SuperAdmin\ReportController::class, 'financial'])->name('financial');
            Route::get('attendance-overview', [App\Http\Controllers\SuperAdmin\ReportController::class, 'attendanceOverview'])->name('attendance-overview');
            Route::get('export/{type}', [App\Http\Controllers\SuperAdmin\ReportController::class, 'export'])->name('export');
        });
        
        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [App\Http\Controllers\SuperAdmin\SettingController::class, 'index'])->name('index');
            Route::put('/general', [App\Http\Controllers\SuperAdmin\SettingController::class, 'updateGeneral'])->name('general');
            Route::put('/academic', [App\Http\Controllers\SuperAdmin\SettingController::class, 'updateAcademic'])->name('academic');
            Route::put('/fees', [App\Http\Controllers\SuperAdmin\SettingController::class, 'updateFees'])->name('fees');
            Route::put('/attendance', [App\Http\Controllers\SuperAdmin\SettingController::class, 'updateAttendance'])->name('attendance');
            Route::put('/notification', [App\Http\Controllers\SuperAdmin\SettingController::class, 'updateNotification'])->name('notification');
            Route::post('/backup', [App\Http\Controllers\SuperAdmin\SettingController::class, 'backup'])->name('backup');
            Route::post('/restore', [App\Http\Controllers\SuperAdmin\SettingController::class, 'restore'])->name('restore');
        });
        
        // System Management
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/logs', [App\Http\Controllers\SuperAdmin\SystemController::class, 'logs'])->name('logs');
            Route::get('/cache', [App\Http\Controllers\SuperAdmin\SystemController::class, 'cache'])->name('cache');
            Route::post('/cache/clear', [App\Http\Controllers\SuperAdmin\SystemController::class, 'clearCache'])->name('cache.clear');
            Route::get('/database', [App\Http\Controllers\SuperAdmin\SystemController::class, 'database'])->name('database');
            Route::get('/phpinfo', [App\Http\Controllers\SuperAdmin\SystemController::class, 'phpinfo'])->name('phpinfo');
        });
        
    });
    
    // ==================== ADMIN ROUTES ====================
    Route::prefix('admin')->name('admin.')->middleware(['user.type:admin'])->group(function () {
        
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Student Management (Limited)
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
        Route::get('students/import', [App\Http\Controllers\Admin\StudentController::class, 'importForm'])->name('students.import-form');
        Route::post('students/import', [App\Http\Controllers\Admin\StudentController::class, 'import'])->name('students.import');
        
        // Teacher Management (Limited)
        Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class);
        Route::get('teachers/data', [App\Http\Controllers\Admin\TeacherController::class, 'getTeachersData'])->name('admin.teachers.data');
        
        // Employee Management (Limited)
        Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);
        
        // Parent Management (Limited)
        Route::resource('parents', App\Http\Controllers\Admin\ParentController::class);
        Route::post('parents/{parent}/toggle-status', [App\Http\Controllers\Admin\ParentController::class, 'toggleStatus'])->name('parents.toggle-status');
        
        // Academic Management
        Route::resource('classes', App\Http\Controllers\Admin\ClassController::class);
        Route::get('/sections/by-class/{class}', [App\Http\Controllers\Admin\SectionController::class, 'getSectionsByClass'])->name('sections.by-class');
        Route::resource('sections', App\Http\Controllers\Admin\SectionController::class);
        Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class);
        Route::post('subjects/assign-to-class', [App\Http\Controllers\Admin\SubjectController::class, 'assignToClass'])->name('subjects.assign-to-class');
        Route::post('subjects/{subject}/remove-from-class', [App\Http\Controllers\Admin\SubjectController::class, 'removeFromClass'])->name('subjects.remove-from-class');
        Route::resource('timetable', App\Http\Controllers\Admin\TimetableController::class);
        Route::get('timetable/class/{class}/section/{section}/edit-grid', [App\Http\Controllers\Admin\TimetableController::class, 'editGrid'])->name('timetable.edit-grid');
        Route::post('timetable/class/{class}/section/{section}/update-grid', [App\Http\Controllers\Admin\TimetableController::class, 'updateGrid'])->name('timetable.update-grid');
        Route::get('timetable/class/{class}/section/{section}/export', [App\Http\Controllers\Admin\TimetableController::class, 'export'])->name('timetable.export');
        Route::get('/get-sections/{class}', function($classId) { 
            $class = \App\Models\Classes::find($classId);
            return response()->json($class ? $class->sections : []);
        })->name('get-sections');
        
        // Attendance
        
        Route::get('attendance/summary', [App\Http\Controllers\Admin\AttendanceController::class, 'summary'])->name('attendance.summary');
        Route::get('attendance/mark', [App\Http\Controllers\Admin\AttendanceController::class, 'mark'])->name('attendance.mark');
        Route::post('attendance/store', [App\Http\Controllers\Admin\AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('attendance/export', [App\Http\Controllers\Admin\AttendanceController::class, 'export'])->name('attendance.export');
        Route::resource('attendance', App\Http\Controllers\Admin\AttendanceController::class);
        
        // Exams
        Route::resource('exams', App\Http\Controllers\Admin\ExamController::class);
        Route::resource('exam-schedules', App\Http\Controllers\Admin\ExamScheduleController::class);
        Route::get('exam-results/bulk/{examSchedule}', [App\Http\Controllers\Admin\ExamResultController::class, 'bulkEntry'])->name('exam-results.bulk');
        Route::post('exam-results/bulk/{examSchedule}', [App\Http\Controllers\Admin\ExamResultController::class, 'bulkStore'])->name('exam-results.bulk-store');
        Route::resource('exam-results', App\Http\Controllers\Admin\ExamResultController::class);
        
        
        // Fees
        Route::resource('fee-structures', App\Http\Controllers\Admin\FeeStructureController::class);
        Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class);
        
        // Library
        Route::resource('books', App\Http\Controllers\Admin\BookController::class);
        Route::get('/book-issues/get-issuers', [App\Http\Controllers\Admin\BookIssueController::class, 'getIssuers'])->name('book-issues.get-issuers');
        Route::get('/book-issues/get-issuer', [App\Http\Controllers\Admin\BookIssueController::class, 'getIssuer'])->name('book-issues.get-issuer');
        Route::post('/book-issues/{bookIssue}/return', [App\Http\Controllers\Admin\BookIssueController::class, 'return'])->name('book-issues.return');
        Route::resource('book-issues', App\Http\Controllers\Admin\BookIssueController::class);
        
        
        // Transport
        Route::get('transport-routes/{transportRoute}/students', [App\Http\Controllers\Admin\TransportRouteController::class, 'students'])->name('transport-routes.students');
        Route::get('transport-routes/{transportRoute}/allocations/create', [App\Http\Controllers\Admin\TransportRouteController::class, 'allocateForm'])->name('transport-routes.allocations.create');
        Route::post('transport-routes/{transportRoute}/allocations', [App\Http\Controllers\Admin\TransportRouteController::class, 'allocateStore'])->name('transport-routes.allocations.store');
        Route::delete('allocations/{allocation}', [App\Http\Controllers\Admin\TransportRouteController::class, 'allocateDestroy'])->name('transport-routes.allocations.destroy');
        Route::resource('transport-routes', App\Http\Controllers\Admin\TransportRouteController::class);
        Route::resource('vehicles', App\Http\Controllers\Admin\VehicleController::class);
        Route::resource('stops', App\Http\Controllers\Admin\StopController::class);
        
        
        // Hostel
        Route::resource('hostels', App\Http\Controllers\Admin\HostelController::class);
        Route::resource('hostel-rooms', App\Http\Controllers\Admin\HostelRoomController::class);
        // Hostel Allocation Management
        Route::prefix('hostel-allocations')->name('hostel-allocations.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\HostelAllocationController::class, 'index'])->name('index');
            Route::get('/pending', [App\Http\Controllers\Admin\HostelAllocationController::class, 'pending'])->name('pending');
            Route::post('{allocation}/approve', [App\Http\Controllers\Admin\HostelAllocationController::class, 'approve'])->name('approve');
            Route::post('{allocation}/reject', [App\Http\Controllers\Admin\HostelAllocationController::class, 'reject'])->name('reject');
            Route::get('/create', [App\Http\Controllers\Admin\HostelAllocationController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\HostelAllocationController::class, 'store'])->name('store');
            Route::delete('{hostelAllocation}', [App\Http\Controllers\Admin\HostelAllocationController::class, 'destroy'])->name('destroy');
        });
        
        // Communication
        Route::resource('announcements', App\Http\Controllers\Admin\AnnouncementController::class);
        Route::resource('events', App\Http\Controllers\Admin\EventController::class);

        // Syllabus Management
        Route::resource('syllabi', App\Http\Controllers\Admin\SyllabusController::class);
        Route::post('syllabi/{syllabus}/topics', [App\Http\Controllers\Admin\SyllabusTopicController::class, 'store'])->name('syllabi.topics.store');
        Route::put('syllabi/topics/{topic}', [App\Http\Controllers\Admin\SyllabusTopicController::class, 'update'])->name('syllabi.topics.update');
        Route::delete('syllabi/topics/{topic}', [App\Http\Controllers\Admin\SyllabusTopicController::class, 'destroy'])->name('syllabi.topics.destroy');
        Route::post('syllabi/{syllabus}/topics/reorder', [App\Http\Controllers\Admin\SyllabusTopicController::class, 'reorder'])->name('syllabi.topics.reorder');
        Route::post('syllabi/topics/{topic}/resources', [App\Http\Controllers\Admin\SyllabusResourceController::class, 'store'])->name('syllabi.topics.resources.store');
        Route::delete('syllabi/resources/{resource}', [App\Http\Controllers\Admin\SyllabusResourceController::class, 'destroy'])->name('syllabi.resources.destroy');


        Route::get('calendar', [App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar.index');
        Route::get('calendar/events', [App\Http\Controllers\Admin\CalendarController::class, 'getEvents'])->name('calendar.events');
        Route::post('calendar/events', [App\Http\Controllers\Admin\CalendarController::class, 'store'])->name('calendar.events.store');
        Route::delete('calendar/events/{event}', [App\Http\Controllers\Admin\CalendarController::class, 'destroy'])->name('calendar.events.destroy');
        
        // Holiday Routes
        Route::resource('holidays', App\Http\Controllers\Admin\HolidayController::class);
        Route::post('holidays/{holiday}/toggle-status', [App\Http\Controllers\Admin\HolidayController::class, 'toggleStatus'])->name('holidays.toggle-status');
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('attendance', [App\Http\Controllers\Admin\ReportController::class, 'attendance'])->name('attendance');
            Route::get('fees', [App\Http\Controllers\Admin\ReportController::class, 'fees'])->name('fees');
            Route::get('exam-results', [App\Http\Controllers\Admin\ReportController::class, 'examResults'])->name('exam-results');
        });

        Route::get('sections/by-class/{classId}', function($classId) {
            $class = \App\Models\Classes::find($classId);
            return response()->json($class ? $class->sections : []);
        })->name('sections.by-class');

        // Settings Routes
        Route::prefix('settings')->name('settings.')->group(function () {
            // Main settings page
            Route::get('/', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('index');
            
            // General Settings
            Route::put('/general', [App\Http\Controllers\Admin\SettingController::class, 'updateGeneral'])->name('general');
            
            // Academic Settings
            Route::put('/academic', [App\Http\Controllers\Admin\SettingController::class, 'updateAcademic'])->name('academic');
            
            // Fees Settings
            Route::put('/fees', [App\Http\Controllers\Admin\SettingController::class, 'updateFees'])->name('fees');
            
            // Attendance Settings
            Route::put('/attendance', [App\Http\Controllers\Admin\SettingController::class, 'updateAttendance'])->name('attendance');
            
            // Notification Settings
            Route::put('/notification', [App\Http\Controllers\Admin\SettingController::class, 'updateNotification'])->name('notification');
            
            // Backup & Restore
            Route::post('/backup', [App\Http\Controllers\Admin\SettingController::class, 'backup'])->name('backup');
            Route::post('/restore', [App\Http\Controllers\Admin\SettingController::class, 'restore'])->name('restore');
            Route::get('/backup-list', [App\Http\Controllers\Admin\SettingController::class, 'backupList'])->name('backup-list');
            Route::delete('/backup/{filename}', [App\Http\Controllers\Admin\SettingController::class, 'deleteBackup'])->name('backup.delete');
            
            // Cache Management
            Route::post('/clear-cache', [App\Http\Controllers\Admin\SettingController::class, 'clearCache'])->name('clear-cache');
            
            // System Information
            Route::get('/system-info', [App\Http\Controllers\Admin\SettingController::class, 'systemInfo'])->name('system-info');
            
            // Email Test
            Route::post('/test-email', [App\Http\Controllers\Admin\SettingController::class, 'testEmail'])->name('test-email');
            
            // Environment Settings
            Route::get('/environment', [App\Http\Controllers\Admin\SettingController::class, 'environment'])->name('environment');
            Route::put('/environment', [App\Http\Controllers\Admin\SettingController::class, 'updateEnvironment'])->name('environment.update');
            
            // Role & Permission Settings
            Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
            Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class);
            
            // Email Settings
            Route::get('/email', [App\Http\Controllers\Admin\SettingController::class, 'emailSettings'])->name('email');
            Route::put('/email', [App\Http\Controllers\Admin\SettingController::class, 'updateEmailSettings'])->name('email.update');
            
            // SMS Settings
            Route::get('/sms', [App\Http\Controllers\Admin\SettingController::class, 'smsSettings'])->name('sms');
            Route::put('/sms', [App\Http\Controllers\Admin\SettingController::class, 'updateSmsSettings'])->name('sms.update');
            
            // Payment Gateway Settings
            Route::get('/payment', [App\Http\Controllers\Admin\SettingController::class, 'paymentSettings'])->name('payment');
            Route::put('/payment', [App\Http\Controllers\Admin\SettingController::class, 'updatePaymentSettings'])->name('payment.update');
            
            // Holiday Settings (separate from calendar)
            Route::get('/holidays', [App\Http\Controllers\Admin\HolidayController::class, 'index'])->name('holidays');
            Route::post('/holidays', [App\Http\Controllers\Admin\HolidayController::class, 'store'])->name('holidays.store');
            Route::put('/holidays/{holiday}', [App\Http\Controllers\Admin\HolidayController::class, 'update'])->name('holidays.update');
            Route::delete('/holidays/{holiday}', [App\Http\Controllers\Admin\HolidayController::class, 'destroy'])->name('holidays.destroy');
            
            // Academic Year Settings
            Route::resource('academic-years', App\Http\Controllers\Admin\AcademicYearController::class);
            Route::post('academic-years/{academicYear}/set-current', [App\Http\Controllers\Admin\AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
            
            // Session Settings
            Route::get('/session', [App\Http\Controllers\Admin\SettingController::class, 'sessionSettings'])->name('session');
            Route::put('/session', [App\Http\Controllers\Admin\SettingController::class, 'updateSessionSettings'])->name('session.update');
            
            // Security Settings
            Route::get('/security', [App\Http\Controllers\Admin\SettingController::class, 'securitySettings'])->name('security');
            Route::put('/security', [App\Http\Controllers\Admin\SettingController::class, 'updateSecuritySettings'])->name('security.update');
            
            // Maintenance Mode
            Route::post('/maintenance', [App\Http\Controllers\Admin\SettingController::class, 'maintenanceMode'])->name('maintenance');
        });
    });
    
    // ==================== TEACHER ROUTES ====================
    Route::prefix('teacher')->name('teacher.')->middleware(['user.type:teacher'])->group(function () {
        
        Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        
        // My Classes
        Route::get('/my-classes', [App\Http\Controllers\Teacher\ClassController::class, 'index'])->name('classes');
        Route::get('/my-timetable', [App\Http\Controllers\Teacher\TimetableController::class, 'index'])->name('timetable');
        
        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('index');
            Route::get('/class/{class}/section/{section}', [App\Http\Controllers\Teacher\AttendanceController::class, 'mark'])->name('mark');
            Route::post('/store', [App\Http\Controllers\Teacher\AttendanceController::class, 'store'])->name('store');
            Route::get('/history', [App\Http\Controllers\Teacher\AttendanceController::class, 'history'])->name('history');
            Route::get('/summary', [App\Http\Controllers\Teacher\AttendanceController::class, 'summary'])->name('summary');
            // AJAX routes for teacher attendance
        });

        Route::get('students/by-class/{classId}', function($classId) {
                return \App\Models\Student::where('class_id', $classId)->with('user')->get()->map(function($student) {
                    return ['id' => $student->id, 'name' => $student->user->name];
                });
        })->name('students.by-class');
        Route::get('sections/by-class/{class}', function($classId) { 
            $class = \App\Models\Classes::find($classId);
            return response()->json($class ? $class->sections : []);
        })->name('sections.by-class');
        Route::get('subjects/by-class/{classId}', function($classId) {
            $teacher = Auth::user();
            $subjects = \App\Models\ClassSubject::where('class_id', $classId)
                ->where('teacher_id', $teacher->id)
                ->with('subject')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->subject->id,
                        'name' => $item->subject->name,
                        'code' => $item->subject->code,
                    ];
                });
            return response()->json($subjects);
        })->name('teacher.subjects.by-class');
        
        // Homework
        Route::resource('homework', App\Http\Controllers\Teacher\HomeworkController::class);
        Route::get('homework/{homework}/submissions', [App\Http\Controllers\Teacher\HomeworkController::class, 'submissions'])->name('homework.submissions');
        Route::post('homework/{homework}/grade/{submission}', [App\Http\Controllers\Teacher\HomeworkController::class, 'grade'])->name('homework.grade');
        Route::get('homework/{homework}/download/{attachmentIndex}', [App\Http\Controllers\Teacher\HomeworkController::class, 'downloadAttachment'])->name('homework.download-attachment');
        Route::get('homework/submission/{submission}/download/{attachmentIndex}', [App\Http\Controllers\Teacher\HomeworkController::class, 'downloadSubmissionAttachment'])->name('homework.download-submission-attachment');
        
        // Exams
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/', [App\Http\Controllers\Teacher\ExamController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Teacher\ExamController::class, 'create'])->name('create');
            Route::post('/store', [App\Http\Controllers\Teacher\ExamController::class, 'store'])->name('store');
            Route::get('/upcoming', [App\Http\Controllers\Teacher\ExamController::class, 'upcoming'])->name('upcoming');
            Route::get('/{examSchedule}/students', [App\Http\Controllers\Teacher\ExamController::class, 'students'])->name('students');
            Route::post('/{examSchedule}/marks', [App\Http\Controllers\Teacher\ExamController::class, 'saveMarks'])->name('save-marks');
            Route::get('/results', [App\Http\Controllers\Teacher\ExamController::class, 'results'])->name('results');
            Route::get('/{examSchedule}/export', [App\Http\Controllers\Teacher\ExamController::class, 'export'])->name('export');
            Route::get('/{examSchedule}/edit', [App\Http\Controllers\Teacher\ExamController::class, 'edit'])->name('edit');
            Route::put('/{examSchedule}', [App\Http\Controllers\Teacher\ExamController::class, 'update'])->name('update');
            Route::delete('/{examSchedule}', [App\Http\Controllers\Teacher\ExamController::class, 'destroy'])->name('destroy');
            Route::get('/subjects/by-class/{classId}', [App\Http\Controllers\Teacher\ExamController::class, 'getSubjectsByClass'])->name('subjects-by-class');
            Route::get('/sections/by-class/{classId}', [App\Http\Controllers\Teacher\ExamController::class, 'getSectionsByClass'])->name('sections-by-class');
        });
        
        // Students
        Route::get('/students', [App\Http\Controllers\Teacher\StudentController::class, 'index'])->name('students');
        Route::get('/students/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'show'])->name('students.show');
        
        // Leave Management
        Route::resource('leaves', App\Http\Controllers\Teacher\LeaveController::class);
        Route::get('leaves/balance', [App\Http\Controllers\Teacher\LeaveController::class, 'balance'])->name('leaves.balance');

        // Syllabus
        Route::get('syllabi', [App\Http\Controllers\Teacher\SyllabusController::class, 'index'])->name('syllabi.index');
        Route::get('syllabi/{syllabus}', [App\Http\Controllers\Teacher\SyllabusController::class, 'show'])->name('syllabi.show');

        // Calendar
        Route::get('calendar', [App\Http\Controllers\Teacher\CalendarController::class, 'index'])->name('calendar.index');
        Route::get('calendar/events', [App\Http\Controllers\Teacher\CalendarController::class, 'getEvents'])->name('calendar.events');
        Route::get('calendar/upcoming', [App\Http\Controllers\Teacher\CalendarController::class, 'upcoming'])->name('calendar.upcoming');
        Route::get('calendar/weekly-schedule', [App\Http\Controllers\Teacher\CalendarController::class, 'weeklySchedule'])->name('calendar.weekly-schedule');

        Route::get('/parent-leave-requests', [App\Http\Controllers\Teacher\ParentLeaveApprovalController::class, 'index'])->name('parent-leave-requests.index');
        Route::get('/parent-leave-requests/{leaveRequest}', [App\Http\Controllers\Teacher\ParentLeaveApprovalController::class, 'show'])->name('parent-leave-requests.show');
        Route::post('/parent-leave-requests/{leaveRequest}/approve', [App\Http\Controllers\Teacher\ParentLeaveApprovalController::class, 'approve'])->name('parent-leave-requests.approve');
        Route::post('/parent-leave-requests/{leaveRequest}/reject', [App\Http\Controllers\Teacher\ParentLeaveApprovalController::class, 'reject'])->name('parent-leave-requests.reject');
        Route::get('/parent-leave-requests/statistics', [App\Http\Controllers\Teacher\ParentLeaveApprovalController::class, 'statistics'])->name('parent-leave-requests.statistics');
    });
    
    // ==================== STUDENT ROUTES ====================
    Route::prefix('student')->name('student.')->middleware(['user.type:student'])->group(function () {
        
        Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        
        // Profile
        Route::get('/profile', [App\Http\Controllers\Student\ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/change-password', [App\Http\Controllers\Student\ProfileController::class, 'changePassword'])->name('profile.change-password');
        
        // Attendance
        Route::get('/attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance');
        Route::get('/attendance/monthly', [App\Http\Controllers\Student\AttendanceController::class, 'monthly'])->name('attendance.monthly');
        Route::get('/attendance/yearly', [App\Http\Controllers\Student\AttendanceController::class, 'yearly'])->name('attendance.yearly');
        
        // Timetable
        Route::get('/timetable', [App\Http\Controllers\Student\TimetableController::class, 'index'])->name('timetable');
        Route::get('/timetable/today', [App\Http\Controllers\Student\TimetableController::class, 'today'])->name('timetable.today');
        
        // Homework
        Route::get('/homework', [App\Http\Controllers\Student\HomeworkController::class, 'index'])->name('homework');
        Route::get('/homework/{homework}', [App\Http\Controllers\Student\HomeworkController::class, 'show'])->name('homework.show');
        Route::post('/homework/{homework}/submit', [App\Http\Controllers\Student\HomeworkController::class, 'submit'])->name('homework.submit');
        Route::get('homework/{homework}/download/{attachmentIndex}', [App\Http\Controllers\Student\HomeworkController::class, 'downloadAttachment'])->name('homework.download-attachment');
        Route::get('homework/submission/{submission}/download/{attachmentIndex}', [App\Http\Controllers\Student\HomeworkController::class, 'downloadSubmissionAttachment'])->name('homework.download-submission-attachment');
        
        // Exams & Results
        Route::get('/exams', [App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams');
        Route::get('/results/summary', [App\Http\Controllers\Student\ResultController::class, 'summary'])->name('results.summary');
        Route::get('/results', [App\Http\Controllers\Student\ResultController::class, 'index'])->name('results');
        Route::get('/results/{exam}', [App\Http\Controllers\Student\ResultController::class, 'show'])->name('results.show');
        
        
        // Fees
        Route::get('/fees', [App\Http\Controllers\Student\FeeController::class, 'index'])->name('fees');
        Route::get('/fees/{fee}', [App\Http\Controllers\Student\FeeController::class, 'show'])->name('fees.show');
        Route::post('/fees/{fee}/pay', [App\Http\Controllers\Student\FeeController::class, 'pay'])->name('fees.pay');
        Route::get('/payments/history', [App\Http\Controllers\Student\FeeController::class, 'paymentHistory'])->name('payments.history');
        Route::get('/fees/receipt/{payment}', [App\Http\Controllers\Student\FeeController::class, 'downloadReceipt'])->name('fees.download-receipt');
        
        // Library
        Route::get('/library/books', [App\Http\Controllers\Student\LibraryController::class, 'books'])->name('library.books');
        Route::get('/library/books/{book}', [App\Http\Controllers\Student\LibraryController::class, 'show'])->name('library.books.show');
        Route::get('/library/issued', [App\Http\Controllers\Student\LibraryController::class, 'issued'])->name('library.issued');
        Route::post('/library/request/{book}', [App\Http\Controllers\Student\LibraryController::class, 'requestIssue'])->name('library.request');
        Route::get('/library/categories', [App\Http\Controllers\Student\LibraryController::class, 'getCategories'])->name('library.categories');
        Route::get('/library/featured', [App\Http\Controllers\Student\LibraryController::class, 'featured'])->name('library.featured');
        
        // Transport
        Route::get('/transport', [App\Http\Controllers\Student\TransportController::class, 'index'])->name('transport');
        Route::get('/transport/routes', [App\Http\Controllers\Student\TransportController::class, 'routes'])->name('transport.routes');
        Route::post('/transport/request', [App\Http\Controllers\Student\TransportController::class, 'requestAllocation'])->name('transport.request');
        Route::get('/transport/route/{route}', [App\Http\Controllers\Student\TransportController::class, 'getRouteDetails'])->name('transport.route-details');
        Route::get('/transport/fare/{stop}', [App\Http\Controllers\Student\TransportController::class, 'getFare'])->name('transport.fare');
        
        // Hostel
        Route::get('/hostel', [App\Http\Controllers\Student\HostelController::class, 'index'])->name('hostel');
        Route::get('/hostel/available-rooms/{hostel}', [App\Http\Controllers\Student\HostelController::class, 'availableRooms'])->name('hostel.available-rooms');
        Route::post('/hostel/request', [App\Http\Controllers\Student\HostelController::class, 'requestAllocation'])->name('hostel.request');
        Route::get('/hostel/all', [App\Http\Controllers\Student\HostelController::class, 'allHostels'])->name('hostel.all');
        Route::get('/hostel/room/{room}/details', [App\Http\Controllers\Student\HostelController::class, 'roomDetails'])->name('hostel.room-details');

        // syllabus
        Route::get('syllabi', [App\Http\Controllers\Student\SyllabusController::class, 'index'])->name('syllabi.index');
        Route::get('syllabi/{syllabus}', [App\Http\Controllers\Student\SyllabusController::class, 'show'])->name('syllabi.show');

        // Calendar
        Route::get('calendar', [App\Http\Controllers\Student\CalendarController::class, 'index'])->name('calendar.index');
        Route::get('calendar/events', [App\Http\Controllers\Student\CalendarController::class, 'getEvents'])->name('calendar.events');

    });
    
    // ==================== PARENT ROUTES ====================
    Route::prefix('parent')->name('parent.')->middleware(['user.type:parent'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');
        
        // Children Management
        Route::get('/children', [App\Http\Controllers\Parent\ChildController::class, 'index'])->name('children');
        Route::get('/children/{student}', [App\Http\Controllers\Parent\ChildController::class, 'show'])->name('children.show');
        
        // Child's Attendance
        Route::get('/children/{student}/attendance', [App\Http\Controllers\Parent\ChildController::class, 'attendance'])->name('children.attendance');
        Route::get('/children/{student}/attendance/monthly', [App\Http\Controllers\Parent\ChildController::class, 'monthlyAttendance'])->name('children.attendance.monthly');
        
        // Child's Results
        Route::get('/children/{student}/results', [App\Http\Controllers\Parent\ChildController::class, 'results'])->name('children.results');
        Route::get('/children/{student}/results/{exam}', [App\Http\Controllers\Parent\ChildController::class, 'resultDetail'])->name('children.results.detail');
        
        // Child's Fees
        Route::get('/children/{student}/fees', [App\Http\Controllers\Parent\ChildController::class, 'fees'])->name('children.fees');
        Route::get('/fees/{fee}/pay', [App\Http\Controllers\Parent\FeeController::class, 'payForm'])->name('fees.pay.form');
        Route::post('/fees/{fee}/pay', [App\Http\Controllers\Parent\FeeController::class, 'processPayment'])->name('fees.pay.process');
        Route::get('/payments/history', [App\Http\Controllers\Parent\FeeController::class, 'paymentHistory'])->name('payments.history');
        Route::get('/payments/receipt/{payment}', [App\Http\Controllers\Parent\FeeController::class, 'downloadReceipt'])->name('payments.receipt');
        
        // Child's Homework
        Route::get('/children/{student}/homework', [App\Http\Controllers\Parent\ChildController::class, 'homework'])->name('children.homework');
        Route::get('/children/{student}/homework/{homework}', [App\Http\Controllers\Parent\ChildController::class, 'homeworkDetail'])->name('children.homework.detail');
        
        // Child's Timetable
        Route::get('/children/{student}/timetable', [App\Http\Controllers\Parent\ChildController::class, 'timetable'])->name('children.timetable');
        
        // Communication with Teachers
        Route::get('/teachers', [App\Http\Controllers\Parent\CommunicationController::class, 'teachers'])->name('teachers');
        Route::get('/messages', [App\Http\Controllers\Parent\CommunicationController::class, 'conversations'])->name('messages');
        Route::get('/messages/{teacher}', [App\Http\Controllers\Parent\CommunicationController::class, 'conversation'])->name('messages.conversation');
        Route::post('/messages/send', [App\Http\Controllers\Parent\CommunicationController::class, 'sendMessage'])->name('messages.send');
        Route::post('/messages/{message}/read', [App\Http\Controllers\Parent\CommunicationController::class, 'markAsRead'])->name('messages.read');
        
        // Notifications
        Route::get('/notifications', [App\Http\Controllers\Parent\CommunicationController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/{notification}/read', [App\Http\Controllers\Parent\CommunicationController::class, 'markNotificationRead'])->name('notifications.read'); 

        Route::resource('leave-requests', App\Http\Controllers\Parent\LeaveRequestController::class);
        Route::post('leave-requests/{leaveRequest}/cancel', [App\Http\Controllers\Parent\LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
        Route::get('leave-requests/{leaveRequest}/download', [App\Http\Controllers\Parent\LeaveRequestController::class, 'downloadAttachment'])->name('leave-requests.download');
        
    });
    
});

// ==================== DOWNLOAD ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/download/receipt/{payment}', [App\Http\Controllers\DownloadController::class, 'receipt'])->name('download.receipt');
    Route::get('/download/id-card/{student}', [App\Http\Controllers\DownloadController::class, 'idCard'])->name('download.id-card');
    Route::get('/download/payslip/{payment}', [App\Http\Controllers\DownloadController::class, 'payslip'])->name('download.payslip');
});

// ==================== ERROR HANDLING ROUTES ====================
Route::fallback(function () {
    return view('errors.404');
});