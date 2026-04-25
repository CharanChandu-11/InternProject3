<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    DashboardController,
    NotificationController,
    MessageController,
    SearchController,
    ReportController
};

Route::get('/sections/by-class/{classId}', [App\Http\Controllers\Api\SectionController::class, 'getSectionsByClass']);

// ==================== PUBLIC APIS ====================
Route::prefix('v1')->group(function () {
    
    // Public Website APIs
    

    // ==================== PUBLIC GALLERY APIS ====================
    Route::prefix('public')->group(function () {
        Route::get('/school-info', [App\Http\Controllers\Api\Public\WebsiteController::class, 'schoolInfo']);
        Route::get('/announcements', [App\Http\Controllers\Api\Public\WebsiteController::class, 'announcements']);
        Route::get('/events', [App\Http\Controllers\Api\Public\WebsiteController::class, 'events']);
        Route::get('/gallery', [App\Http\Controllers\Api\Public\WebsiteController::class, 'gallery']);
        Route::post('/contact', [App\Http\Controllers\Api\Public\WebsiteController::class, 'contact']);
        Route::post('/admission-inquiry', [App\Http\Controllers\Api\Public\WebsiteController::class, 'admissionInquiry']);

        // gallery subs
        Route::get('/gallery/featured', [App\Http\Controllers\Api\Public\GalleryController::class, 'featured']);
        Route::get('/gallery/categories', [App\Http\Controllers\Api\Public\GalleryController::class, 'categories']);
        Route::get('/gallery/category/{category}', [App\Http\Controllers\Api\Public\GalleryController::class, 'byCategory']);
        Route::get('/gallery/{id}', [App\Http\Controllers\Api\Public\GalleryController::class, 'show']);
    });

    

    // Authentication APIs
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    
    // ==================== PROTECTED APIS ====================
    Route::middleware('auth:sanctum')->group(function () {
        
        // Common APIs for all users
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);
        Route::put('/auth/update-profile', [AuthController::class, 'updateProfile']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
        
        // Dashboard APIs
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/notifications', [DashboardController::class, 'notifications']);
        Route::get('/dashboard/upcoming-events', [DashboardController::class, 'upcomingEvents']);
        
        // Notification APIs
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        
        // Message APIs
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']);
        Route::get('/messages/{id}', [MessageController::class, 'show']);
        Route::get('/messages/conversation/{userId}', [MessageController::class, 'conversation']);
        
        // Search APIs
        Route::get('/search', [SearchController::class, 'global']);
        Route::get('/search/students', [SearchController::class, 'students']);
        Route::get('/search/teachers', [SearchController::class, 'teachers']);
        
        // Report APIs
        Route::get('/reports/attendance', [ReportController::class, 'attendance']);
        Route::get('/reports/fees', [ReportController::class, 'fees']);
        Route::get('/reports/exam-results', [ReportController::class, 'examResults']);
        Route::get('/reports/student-performance', [ReportController::class, 'studentPerformance']);
        Route::get('/reports/teacher-performance', [ReportController::class, 'teacherPerformance']);
        Route::get('/reports/financial', [ReportController::class, 'financial']);
        Route::get('/reports/attendance-overview', [ReportController::class, 'attendanceOverview']);
        
        // ==================== SUPER ADMIN APIS ====================
        Route::prefix('super-admin')->middleware('user.type:super_admin')->group(function () {

            Route::apiResource('gallery', App\Http\Controllers\Api\SuperAdmin\GalleryController::class);
            Route::post('/gallery/{gallery}/toggle-featured', [App\Http\Controllers\Api\SuperAdmin\GalleryController::class, 'toggleFeatured']);
            Route::post('/gallery/update-order', [App\Http\Controllers\Api\SuperAdmin\GalleryController::class, 'updateOrder']);
            Route::post('/gallery/bulk-delete', [App\Http\Controllers\Api\SuperAdmin\GalleryController::class, 'bulkDestroy']);
            Route::get('/gallery-categories', [App\Http\Controllers\Api\SuperAdmin\GalleryController::class, 'categories']);
    
            
            // Dashboard
            Route::get('/dashboard', [App\Http\Controllers\Api\SuperAdmin\DashboardController::class, 'index']);
            
            // User Management
            Route::apiResource('users', App\Http\Controllers\Api\SuperAdmin\UserController::class);
            Route::post('users/{user}/toggle-status', [App\Http\Controllers\Api\SuperAdmin\UserController::class, 'toggleStatus']);
            Route::post('users/bulk-import', [App\Http\Controllers\Api\SuperAdmin\UserController::class, 'bulkImport']);
            Route::get('users/export', [App\Http\Controllers\Api\SuperAdmin\UserController::class, 'export']);
            
            // Student Management
            Route::apiResource('students', App\Http\Controllers\Api\SuperAdmin\StudentController::class);
            Route::post('students/{student}/promote', [App\Http\Controllers\Api\SuperAdmin\StudentController::class, 'promote']);
            Route::post('students/{student}/generate-id-card', [App\Http\Controllers\Api\SuperAdmin\StudentController::class, 'generateIdCard']);
            Route::post('students/bulk-import', [App\Http\Controllers\Api\SuperAdmin\StudentController::class, 'bulkImport']);
            Route::get('students/export', [App\Http\Controllers\Api\SuperAdmin\StudentController::class, 'export']);
            
            // Teacher Management
            Route::apiResource('teachers', App\Http\Controllers\Api\SuperAdmin\TeacherController::class);
            Route::post('teachers/{teacher}/assign-class', [App\Http\Controllers\Api\SuperAdmin\TeacherController::class, 'assignClass']);
            
            // Employee Management
            Route::apiResource('employees', App\Http\Controllers\Api\SuperAdmin\EmployeeController::class);
            
            // Parent Management
            Route::apiResource('parents', App\Http\Controllers\Api\SuperAdmin\ParentController::class);
            
            // Academic Management
            Route::apiResource('academic-years', App\Http\Controllers\Api\SuperAdmin\AcademicYearController::class);
            Route::apiResource('classes', App\Http\Controllers\Api\SuperAdmin\ClassController::class);
            Route::apiResource('sections', App\Http\Controllers\Api\SuperAdmin\SectionController::class);
            Route::apiResource('subjects', App\Http\Controllers\Api\SuperAdmin\SubjectController::class);
            Route::apiResource('timetable', App\Http\Controllers\Api\SuperAdmin\TimetableController::class);
            
            // Attendance
            Route::apiResource('attendance', App\Http\Controllers\Api\SuperAdmin\AttendanceController::class);
            Route::get('attendance/summary', [App\Http\Controllers\Api\SuperAdmin\AttendanceController::class, 'summary']);
            
            // Examinations
            Route::apiResource('exams', App\Http\Controllers\Api\SuperAdmin\ExamController::class);
            Route::apiResource('exam-schedules', App\Http\Controllers\Api\SuperAdmin\ExamScheduleController::class);
            Route::apiResource('exam-results', App\Http\Controllers\Api\SuperAdmin\ExamResultController::class);
            
            // Fees Management
            Route::apiResource('fee-categories', App\Http\Controllers\Api\SuperAdmin\FeeCategoryController::class);
            Route::apiResource('fee-structures', App\Http\Controllers\Api\SuperAdmin\FeeStructureController::class);
            Route::apiResource('student-fees', App\Http\Controllers\Api\SuperAdmin\StudentFeeController::class);
            Route::apiResource('payments', App\Http\Controllers\Api\SuperAdmin\PaymentController::class);
            
            // Library
            Route::apiResource('books', App\Http\Controllers\Api\SuperAdmin\BookController::class);
            Route::apiResource('book-issues', App\Http\Controllers\Api\SuperAdmin\BookIssueController::class);
            
            // Transport
            Route::apiResource('transport-routes', App\Http\Controllers\Api\SuperAdmin\TransportRouteController::class);
            Route::apiResource('vehicles', App\Http\Controllers\Api\SuperAdmin\VehicleController::class);
            Route::apiResource('stops', App\Http\Controllers\Api\SuperAdmin\StopController::class);
            
            // Hostel
            Route::apiResource('hostels', App\Http\Controllers\Api\SuperAdmin\HostelController::class);
            Route::apiResource('hostel-rooms', App\Http\Controllers\Api\SuperAdmin\HostelRoomController::class);
            Route::apiResource('hostel-allocations', App\Http\Controllers\Api\SuperAdmin\HostelAllocationController::class);
            
            // Communication
            Route::apiResource('announcements', App\Http\Controllers\Api\SuperAdmin\AnnouncementController::class);
            Route::apiResource('events', App\Http\Controllers\Api\SuperAdmin\EventController::class);
            
            // Settings
            Route::get('settings', [App\Http\Controllers\Api\SuperAdmin\SettingController::class, 'index']);
            Route::put('settings', [App\Http\Controllers\Api\SuperAdmin\SettingController::class, 'update']);
            Route::post('settings/backup', [App\Http\Controllers\Api\SuperAdmin\SettingController::class, 'backup']);
            Route::post('settings/restore', [App\Http\Controllers\Api\SuperAdmin\SettingController::class, 'restore']);
            
            // Reports
            Route::get('reports/student-performance', [App\Http\Controllers\Api\SuperAdmin\ReportController::class, 'studentPerformance']);
            Route::get('reports/teacher-performance', [App\Http\Controllers\Api\SuperAdmin\ReportController::class, 'teacherPerformance']);
            Route::get('reports/financial', [App\Http\Controllers\Api\SuperAdmin\ReportController::class, 'financial']);
            Route::get('reports/attendance-overview', [App\Http\Controllers\Api\SuperAdmin\ReportController::class, 'attendanceOverview']);
        });
        
        // ==================== ADMIN APIS ====================
        Route::prefix('admin')->middleware('user.type:admin')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
            // Similar structure as super-admin but with limited permissions
        });
        
        // ==================== TEACHER APIS ====================
        Route::prefix('teacher')->middleware('user.type:teacher')->group(function () {

            Route::get('/dashboard', [App\Http\Controllers\Api\Teacher\DashboardController::class, 'index']);
            Route::get('/dashboard/stats', [App\Http\Controllers\Api\Teacher\DashboardController::class, 'stats']);
            
            // Profile
            Route::get('/profile', [App\Http\Controllers\Api\Teacher\ProfileController::class, 'show']);
            Route::put('/profile', [App\Http\Controllers\Api\Teacher\ProfileController::class, 'update']);
            Route::post('/profile/change-password', [App\Http\Controllers\Api\Teacher\ProfileController::class, 'changePassword']);
            
            // Classes
            Route::get('/classes', [App\Http\Controllers\Api\Teacher\ClassController::class, 'index']);
            Route::get('/classes/{classId}/students', [App\Http\Controllers\Api\Teacher\ClassController::class, 'students']);
            Route::get('/classes/{classId}/subjects', [App\Http\Controllers\Api\Teacher\ClassController::class, 'subjects']);
            Route::get('/classes/{classId}/sections', [App\Http\Controllers\Api\Teacher\ClassController::class, 'sections']);
            
            // Timetable
            Route::get('/timetable', [App\Http\Controllers\Api\Teacher\ClassController::class, 'timetable']);
            Route::get('/timetable/today', [App\Http\Controllers\Api\Teacher\ClassController::class, 'todayTimetable']);
            Route::get('/timetable/weekly', [App\Http\Controllers\Api\Teacher\ClassController::class, 'weeklyTimetable']);
            
            // Attendance
            Route::get('/attendance/classes', [App\Http\Controllers\Api\Teacher\AttendanceController::class, 'classes']);
            Route::get('/attendance/students/{classId}/{sectionId}', [App\Http\Controllers\Api\Teacher\AttendanceController::class, 'students']);
            Route::post('/attendance/mark', [App\Http\Controllers\Api\Teacher\AttendanceController::class, 'mark']);
            Route::get('/attendance/history', [App\Http\Controllers\Api\Teacher\AttendanceController::class, 'history']);
            Route::get('/attendance/summary', [App\Http\Controllers\Api\Teacher\AttendanceController::class, 'summary']);
            Route::get('/attendance/report', [App\Http\Controllers\Api\Teacher\AttendanceController::class, 'report']);
            
            // Homework
            Route::get('/homework', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'index']);
            Route::post('/homework', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'store']);
            Route::get('/homework/{homework}', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'show']);
            Route::put('/homework/{homework}', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'update']);
            Route::delete('/homework/{homework}', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'destroy']);
            Route::get('/homework/{homework}/submissions', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'submissions']);
            Route::post('/homework/{homework}/grade/{submission}', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'grade']);
            Route::get('/homework/{homework}/download/{attachmentIndex}', [App\Http\Controllers\Api\Teacher\HomeworkController::class, 'downloadAttachment']);
            
            // Exams
            Route::get('/exams', [App\Http\Controllers\Api\Teacher\ExamController::class, 'index']);
            Route::post('/exams', [App\Http\Controllers\Api\Teacher\ExamController::class, 'store']);
            Route::get('/exams/upcoming', [App\Http\Controllers\Api\Teacher\ExamController::class, 'upcoming']);
            Route::get('/exams/results', [App\Http\Controllers\Api\Teacher\ExamController::class, 'results']);
            Route::get('/exams/{examSchedule}', [App\Http\Controllers\Api\Teacher\ExamController::class, 'show']);
            Route::put('/exams/{examSchedule}', [App\Http\Controllers\Api\Teacher\ExamController::class, 'update']);
            Route::delete('/exams/{examSchedule}', [App\Http\Controllers\Api\Teacher\ExamController::class, 'destroy']);
            Route::get('/exams/{examSchedule}/students', [App\Http\Controllers\Api\Teacher\ExamController::class, 'students']);
            Route::post('/exams/{examSchedule}/marks', [App\Http\Controllers\Api\Teacher\ExamController::class, 'saveMarks']);
            Route::get('/exams/{examSchedule}/export', [App\Http\Controllers\Api\Teacher\ExamController::class, 'export']);
            Route::get('/exam-types/dropdown', [App\Http\Controllers\Api\Teacher\ExamController::class, 'dropdown']);
            
            // Students
            Route::get('/students', [App\Http\Controllers\Api\Teacher\StudentController::class, 'index']);
            Route::get('/students/{student}', [App\Http\Controllers\Api\Teacher\StudentController::class, 'show']);
            Route::get('/students/{student}/attendance', [App\Http\Controllers\Api\Teacher\StudentController::class, 'attendance']);
            Route::get('/students/{student}/results', [App\Http\Controllers\Api\Teacher\StudentController::class, 'results']);
            Route::get('/students/{student}/performance', [App\Http\Controllers\Api\Teacher\StudentController::class, 'performance']);
            
            // Leaves
            Route::get('/leaves', [App\Http\Controllers\Api\Teacher\LeaveController::class, 'index']);
            Route::post('/leaves', [App\Http\Controllers\Api\Teacher\LeaveController::class, 'store']);
            Route::get('/leaves/balance', [App\Http\Controllers\Api\Teacher\LeaveController::class, 'balance']);
            Route::get('/leaves/types', [App\Http\Controllers\Api\Teacher\LeaveController::class, 'leaveTypes']);
            Route::get('/leaves/{leave}', [App\Http\Controllers\Api\Teacher\LeaveController::class, 'show']);
            Route::put('/leaves/{leave}', [App\Http\Controllers\Api\Teacher\LeaveController::class, 'update']);
            Route::delete('/leaves/{leave}', [App\Http\Controllers\Api\Teacher\LeaveController::class, 'destroy']);
            
            
            // Syllabus
            Route::get('/syllabi', [App\Http\Controllers\Api\Teacher\SyllabusController::class, 'index']);
            Route::get('/syllabi/{syllabus}', [App\Http\Controllers\Api\Teacher\SyllabusController::class, 'show']);
            Route::get('/syllabi/topics/{topic}/resources', [App\Http\Controllers\Api\Teacher\SyllabusController::class, 'topicResources']);
            Route::get('/syllabi/resources/{resource}/download', [App\Http\Controllers\Api\Teacher\SyllabusController::class, 'downloadResource']);
            
            // Calendar
            Route::get('/calendar/events', [App\Http\Controllers\Api\Teacher\CalendarController::class, 'getEvents']);
            Route::get('/calendar/upcoming', [App\Http\Controllers\Api\Teacher\CalendarController::class, 'upcoming']);
            Route::get('/calendar/weekly-schedule', [App\Http\Controllers\Api\Teacher\CalendarController::class, 'weeklySchedule']);

            Route::get('/parent-leave-requests', [App\Http\Controllers\Api\Teacher\ParentLeaveApprovalController::class, 'index']);
            Route::get('/parent-leave-requests/statistics', [App\Http\Controllers\Api\Teacher\ParentLeaveApprovalController::class, 'statistics']);
            Route::get('/parent-leave-requests/{leaveRequest}', [App\Http\Controllers\Api\Teacher\ParentLeaveApprovalController::class, 'show']);
            Route::post('/parent-leave-requests/{leaveRequest}/approve', [App\Http\Controllers\Api\Teacher\ParentLeaveApprovalController::class, 'approve']);
            Route::post('/parent-leave-requests/{leaveRequest}/reject', [App\Http\Controllers\Api\Teacher\ParentLeaveApprovalController::class, 'reject']);
            
        });
        
        // ==================== STUDENT APIS ====================
        Route::middleware(['auth:sanctum', 'user.type:student'])->prefix('student')->name('api.student.')->group(function () {

            // Dashboard
            Route::get('/dashboard', [App\Http\Controllers\Api\Student\DashboardController::class, 'index'])->name('dashboard');

            // Profile
            Route::get('/profile', [App\Http\Controllers\Api\Student\ProfileController::class, 'show'])->name('profile.show');
            Route::put('/profile', [App\Http\Controllers\Api\Student\ProfileController::class, 'update'])->name('profile.update');
            Route::post('/profile/change-password', [App\Http\Controllers\Api\Student\ProfileController::class, 'changePassword'])->name('profile.change-password');

            // Attendance
            Route::get('/attendance', [App\Http\Controllers\Api\Student\AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('/attendance/monthly', [App\Http\Controllers\Api\Student\AttendanceController::class, 'monthly'])->name('attendance.monthly');
            Route::get('/attendance/yearly', [App\Http\Controllers\Api\Student\AttendanceController::class, 'yearly'])->name('attendance.yearly');

            // Timetable
            Route::get('/timetable', [App\Http\Controllers\Api\Student\TimetableController::class, 'index'])->name('timetable.index');
            Route::get('/timetable/today', [App\Http\Controllers\Api\Student\TimetableController::class, 'today'])->name('timetable.today');

            // Homework
            Route::get('/homework', [App\Http\Controllers\Api\Student\HomeworkController::class, 'index'])->name('homework.index');
            Route::get('/homework/{homework}', [App\Http\Controllers\Api\Student\HomeworkController::class, 'show'])->name('homework.show');
            Route::post('/homework/{homework}/submit', [App\Http\Controllers\Api\Student\HomeworkController::class, 'submit'])->name('homework.submit');
            Route::get('/homework/{homework}/download/{attachmentIndex}', [App\Http\Controllers\Api\Student\HomeworkController::class, 'downloadAttachment'])->name('homework.download-attachment');
            Route::get('/homework/submission/{submission}/download/{attachmentIndex}', [App\Http\Controllers\Api\Student\HomeworkController::class, 'downloadSubmissionAttachment'])->name('homework.download-submission-attachment');

            // Exams & Results
            Route::get('/exams', [App\Http\Controllers\Api\Student\ExamController::class, 'index'])->name('exams.index');
            Route::get('/results', [App\Http\Controllers\Api\Student\ResultController::class, 'index'])->name('results.index');
            Route::get('/results/summary', [App\Http\Controllers\Api\Student\ResultController::class, 'summary'])->name('results.summary');
            Route::get('/results/{exam}', [App\Http\Controllers\Api\Student\ResultController::class, 'show'])->name('results.show');
            

            // Fees
            Route::get('/fees', [App\Http\Controllers\Api\Student\FeeController::class, 'index'])->name('fees.index');
            Route::get('/fees/{fee}', [App\Http\Controllers\Api\Student\FeeController::class, 'show'])->name('fees.show');
            Route::post('/fees/{fee}/pay', [App\Http\Controllers\Api\Student\FeeController::class, 'pay'])->name('fees.pay');
            Route::get('/payments/history', [App\Http\Controllers\Api\Student\FeeController::class, 'paymentHistory'])->name('payments.history');
            Route::get('/fees/receipt/{payment}', [App\Http\Controllers\Api\Student\FeeController::class, 'downloadReceipt'])->name('fees.download-receipt');

            // Library
            Route::get('/library/books', [App\Http\Controllers\Api\Student\LibraryController::class, 'books'])->name('library.books');
            Route::get('/library/books/{book}', [App\Http\Controllers\Api\Student\LibraryController::class, 'show'])->name('library.books.show');
            Route::get('/library/issued', [App\Http\Controllers\Api\Student\LibraryController::class, 'issued'])->name('library.issued');
            Route::post('/library/request/{book}', [App\Http\Controllers\Api\Student\LibraryController::class, 'requestIssue'])->name('library.request');
            Route::get('/library/categories', [App\Http\Controllers\Api\Student\LibraryController::class, 'getCategories'])->name('library.categories');
            Route::get('/library/featured', [App\Http\Controllers\Api\Student\LibraryController::class, 'featured'])->name('library.featured');

            // Transport
            Route::get('/transport', [App\Http\Controllers\Api\Student\TransportController::class, 'index'])->name('transport.index');
            Route::get('/transport/routes', [App\Http\Controllers\Api\Student\TransportController::class, 'routes'])->name('transport.routes');
            Route::post('/transport/request', [App\Http\Controllers\Api\Student\TransportController::class, 'requestAllocation'])->name('transport.request');
            Route::get('/transport/route/{route}', [App\Http\Controllers\Api\Student\TransportController::class, 'getRouteDetails'])->name('transport.route-details');
            Route::get('/transport/fare/{stop}', [App\Http\Controllers\Api\Student\TransportController::class, 'getFare'])->name('transport.fare');

            // Hostel
            Route::get('/hostel', [App\Http\Controllers\Api\Student\HostelController::class, 'index'])->name('hostel.index');
            Route::get('/hostel/available-rooms/{hostel}', [App\Http\Controllers\Api\Student\HostelController::class, 'availableRooms'])->name('hostel.available-rooms');
            Route::post('/hostel/request', [App\Http\Controllers\Api\Student\HostelController::class, 'requestAllocation'])->name('hostel.request');
            Route::get('/hostel/all', [App\Http\Controllers\Api\Student\HostelController::class, 'allHostels'])->name('hostel.all');
            Route::get('/hostel/room/{room}/details', [App\Http\Controllers\Api\Student\HostelController::class, 'roomDetails'])->name('hostel.room-details');

            // Syllabus API
            Route::get('/syllabi', [App\Http\Controllers\Api\Student\SyllabusController::class, 'index']);
            Route::get('/syllabi/{syllabus}', [App\Http\Controllers\Api\Student\SyllabusController::class, 'show']);
            
            // Calendar API
            Route::get('/calendar/events', [App\Http\Controllers\Api\Student\CalendarController::class, 'getEvents']);
            Route::get('/calendar/upcoming', [App\Http\Controllers\Api\Student\CalendarController::class, 'upcoming']);
            Route::get('/calendar/holidays', [App\Http\Controllers\Api\Student\CalendarController::class, 'holidays']);

        });
        
        // ==================== PARENT APIS ====================
        Route::prefix('parent')->middleware('user.type:parent')->group(function () {
            
            // Dashboard
            Route::get('/dashboard', [App\Http\Controllers\Api\Parent\DashboardController::class, 'index']);
            Route::get('/dashboard/stats', [App\Http\Controllers\Api\Parent\DashboardController::class, 'stats']);
            
            // Children
            Route::get('/children', [App\Http\Controllers\Api\Parent\ChildController::class, 'index']);
            Route::get('/children/{student}', [App\Http\Controllers\Api\Parent\ChildController::class, 'show']);
            
            // Child's Attendance
            Route::get('/children/{student}/attendance', [App\Http\Controllers\Api\Parent\ChildController::class, 'attendance']);
            Route::get('/children/{student}/attendance/monthly', [App\Http\Controllers\Api\Parent\ChildController::class, 'monthlyAttendance']);
            Route::get('/children/{student}/attendance/summary', [App\Http\Controllers\Api\Parent\ChildController::class, 'attendanceSummary']);
            
            // Child's Results
            Route::get('/children/{student}/results', [App\Http\Controllers\Api\Parent\ChildController::class, 'results']);
            Route::get('/children/{student}/results/summary', [App\Http\Controllers\Api\Parent\ChildController::class, 'resultSummary']);
            Route::get('/children/{student}/results/{exam}', [App\Http\Controllers\Api\Parent\ChildController::class, 'resultDetail']);
            
            
            // Child's Fees
            Route::get('/children/{student}/fees', [App\Http\Controllers\Api\Parent\ChildController::class, 'fees']);
            Route::post('/fees/{fee}/pay', [App\Http\Controllers\Api\Parent\FeeController::class, 'pay']);
            Route::get('/payments/history', [App\Http\Controllers\Api\Parent\FeeController::class, 'paymentHistory']);
            Route::get('/payments/receipt/{payment}', [App\Http\Controllers\Api\Parent\FeeController::class, 'downloadReceipt']);
            
            // Child's Homework
            Route::get('/children/{student}/homework', [App\Http\Controllers\Api\Parent\ChildController::class, 'homework']);
            Route::get('/children/{student}/homework/{homework}', [App\Http\Controllers\Api\Parent\ChildController::class, 'homeworkDetail']);
            
            // Child's Timetable
            Route::get('/children/{student}/timetable', [App\Http\Controllers\Api\Parent\ChildController::class, 'timetable']);
            
            // Communication
            Route::get('/teachers', [App\Http\Controllers\Api\Parent\CommunicationController::class, 'teachers']);
            Route::get('/messages', [App\Http\Controllers\Api\Parent\CommunicationController::class, 'conversations']);
            Route::get('/messages/{teacher}', [App\Http\Controllers\Api\Parent\CommunicationController::class, 'conversation']);
            Route::post('/messages/send', [App\Http\Controllers\Api\Parent\CommunicationController::class, 'sendMessage']);
            Route::post('/messages/{message}/read', [App\Http\Controllers\Api\Parent\CommunicationController::class, 'markAsRead']);
            
            // Notifications
            Route::get('/notifications', [App\Http\Controllers\Api\Parent\CommunicationController::class, 'notifications']);
            Route::post('/notifications/{notification}/read', [App\Http\Controllers\Api\Parent\CommunicationController::class, 'markNotificationRead']);

            // Leave Request Routes
            Route::get('/leave-requests', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'index']);
            Route::get('/leave-requests/children', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'children']);
            Route::get('/leave-requests/types', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'leaveTypes']);
            Route::post('/leave-requests', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'store']);
            Route::get('/leave-requests/{leaveRequest}', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'show']);
            Route::put('/leave-requests/{leaveRequest}', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'update']);
            Route::post('/leave-requests/{leaveRequest}/cancel', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'cancel']);
            Route::get('/leave-requests/{leaveRequest}/download', [App\Http\Controllers\Api\Parent\LeaveRequestController::class, 'download']);
        });
        
        // ==================== EMPLOYEE APIS ====================
        Route::prefix('employee')->middleware('user.type:employee')->group(function () {
            
            // Dashboard
            Route::get('/dashboard', [App\Http\Controllers\Api\Employee\DashboardController::class, 'index']);
            
            // Attendance
            Route::get('/attendance', [App\Http\Controllers\Api\Employee\AttendanceController::class, 'index']);
            Route::post('/attendance/mark', [App\Http\Controllers\Api\Employee\AttendanceController::class, 'mark']);
            
            // Leave Management
            Route::apiResource('leaves', App\Http\Controllers\Api\Employee\LeaveController::class);
            
            // Salary
            Route::get('/salary', [App\Http\Controllers\Api\Employee\SalaryController::class, 'index']);
            Route::get('/salary/{payment}', [App\Http\Controllers\Api\Employee\SalaryController::class, 'show']);
            
            // Tasks
            Route::get('/tasks', [App\Http\Controllers\Api\Employee\TaskController::class, 'index']);
            Route::put('/tasks/{task}/status', [App\Http\Controllers\Api\Employee\TaskController::class, 'updateStatus']);
        });
    });
});