<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Users & Authentication Tables
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique()->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->enum('user_type', ['super_admin', 'admin', 'employee', 'teacher', 'student', 'parent']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 2. User Profiles (Extend user info)
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('blood_group')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->nullable();
            $table->text('permanent_address')->nullable();
            $table->text('current_address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->timestamps();
        });

        // 3. Academic Structure
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('numeric_name')->nullable();
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('class_teacher_id')->nullable()->constrained('users');
            $table->integer('capacity')->default(30);
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->integer('capacity')->default(30);
            $table->timestamps();
        });

        // 4. Subjects & Curriculum
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['core', 'elective', 'language', 'practical']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('teacher_id')->constrained('users');
            $table->boolean('is_lab_required')->default(false);
            $table->integer('theory_marks')->default(100);
            $table->integer('practical_marks')->default(0);
            $table->timestamps();
        });

        // 5. Student Management
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique();
            $table->date('admission_date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->string('roll_number')->nullable();
            $table->string('previous_school')->nullable();
            $table->decimal('previous_grade', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('parent_type', ['father', 'mother', 'guardian'])->nullable(); // Make nullable if you want
            // OR if you want it required, keep as is and provide value in seeder
            $table->string('occupation')->nullable();
            $table->string('office_address')->nullable();
            $table->string('office_phone')->nullable();
            $table->timestamps();
        });

        Schema::create('student_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->enum('relationship', ['father', 'mother', 'guardian']);
            $table->boolean('is_primary_contact')->default(false);
            $table->timestamps();
            
            // Prevent duplicate entries
            $table->unique(['student_id', 'parent_id']);
        });

        // 6. Employee Management
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'probation']);
            $table->date('joining_date');
            $table->string('department');
            $table->string('designation');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('qualification')->nullable();
            $table->integer('experience_years')->nullable();
            $table->timestamps();
        });

        // 7. Attendance System
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->time('school_start_time');
            $table->time('school_end_time');
            $table->integer('late_threshold_minutes')->default(15);
            $table->boolean('enable_biometric')->default(false);
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->morphs('attendable'); // For students, employees, teachers
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday']);
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->constrained('users');
            $table->timestamps();
        });

        // 8. Timetable Management
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_break')->default(false);
            $table->timestamps();
        });

        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->foreignId('time_slot_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('teacher_id')->constrained('users');
            $table->string('room_number')->nullable();
            $table->timestamps();
        });

        // 9. Examination System
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('exam_type_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed']);
            $table->timestamps();
        });

        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('total_marks');
            $table->integer('passing_marks');
            $table->string('room_number')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained();
            $table->foreignId('student_id')->constrained('students');
            $table->integer('theory_marks_obtained')->nullable();
            $table->integer('practical_marks_obtained')->nullable();
            $table->integer('total_marks_obtained');
            $table->enum('grade', ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'])->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 10. Fee Management
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('fee_category_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['monthly', 'quarterly', 'half_yearly', 'yearly', 'one_time']);
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
        });

        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('fee_structure_id')->constrained();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue']);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('student_fee_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'online']);
            $table->string('transaction_id')->nullable();
            $table->date('payment_date');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded']);
            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
        });

        // 11. Library Management
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->unique()->nullable();
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('category');
            $table->integer('quantity')->default(1);
            $table->integer('available_quantity')->default(1);
            $table->string('shelf_location')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained();
            $table->morphs('issuable'); // For students, employees, teachers
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost']);
            $table->decimal('late_fee', 8, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 12. Transport Management
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('route_number')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_route_id')->constrained();
            $table->string('stop_name');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();
            $table->time('pickup_time');
            $table->time('drop_time');
            $table->decimal('fee', 10, 2);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number')->unique();
            $table->string('vehicle_type');
            $table->string('model');
            $table->integer('capacity');
            $table->string('driver_name');
            $table->string('driver_license');
            $table->string('driver_phone');
            $table->string('insurance_expiry')->nullable();
            $table->timestamps();
        });

        Schema::create('route_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_route_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->enum('shift', ['morning', 'evening']);
            $table->timestamps();
        });

        Schema::create('student_transport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('transport_route_id')->constrained();
            $table->foreignId('stop_id')->constrained();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 13. Hostel Management
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['boys', 'girls', 'co_ed']);
            $table->string('warden_name');
            $table->string('warden_phone');
            $table->string('address');
            $table->integer('total_rooms');
            $table->timestamps();
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained();
            $table->string('room_number');
            $table->enum('room_type', ['single', 'double', 'triple', 'dormitory']);
            $table->integer('capacity');
            $table->integer('occupied')->default(0);
            $table->decimal('fee_per_month', 10, 2);
            $table->timestamps();
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('hostel_room_id')->constrained();
            $table->date('allocation_date');
            $table->date('leave_date')->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->timestamps();
        });

        // 14. Communication System
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['email', 'sms', 'push', 'in_app']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->json('recipients'); // Store user IDs or roles
            $table->json('sent_to')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('audience', ['all', 'students', 'parents', 'teachers', 'employees', 'specific_classes']);
            $table->json('specific_classes')->nullable();
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->constrained('users');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 15. Event Management
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['academic', 'cultural', 'sports', 'holiday', 'meeting']);
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue');
            $table->enum('audience', ['all', 'students', 'teachers', 'staff']);
            $table->json('participants')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // 16. Inventory Management
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('inventory_categories');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->integer('minimum_quantity')->default(5);
            $table->integer('maximum_quantity')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->string('unit');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items');
            $table->enum('type', ['purchase', 'issue', 'return', 'adjustment']);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->morphs('transactionable', 'inv_trans_tran_index'); // For issues to employees, students etc.
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // 17. Leave Management
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('days_allowed');
            $table->enum('applicable_for', ['teacher', 'employee', 'both']);
            $table->timestamps();
        });

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('leave_type_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled']);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->date('approved_date')->nullable();
            $table->timestamps();
        });

        // 18. Payroll Management
        Schema::create('salary_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('house_rent_allowance', 10, 2)->nullable();
            $table->decimal('dearness_allowance', 10, 2)->nullable();
            $table->decimal('travel_allowance', 10, 2)->nullable();
            $table->decimal('medical_allowance', 10, 2)->nullable();
            $table->decimal('special_allowance', 10, 2)->nullable();
            $table->decimal('provident_fund', 10, 2)->nullable();
            $table->decimal('professional_tax', 10, 2)->nullable();
            $table->decimal('income_tax', 10, 2)->nullable();
            $table->decimal('total_earnings', 10, 2);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->timestamps();
        });

        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('salary_template_id')->constrained();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('salary_template_id')->constrained();
            $table->string('payment_month'); // YYYY-MM
            $table->integer('working_days');
            $table->integer('present_days');
            $table->integer('leave_days');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('allowances', 10, 2);
            $table->decimal('deductions', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->decimal('total_payment', 10, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque']);
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['paid', 'pending', 'processing']);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 19. Homework Management
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('class_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('teacher_id')->constrained('users');
            $table->date('submission_date');
            $table->time('submission_time')->nullable();
            $table->json('attachments')->nullable();
            $table->integer('total_marks')->nullable();
            $table->enum('status', ['active', 'expired', 'draft']);
            $table->timestamps();
        });

        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained();
            $table->foreignId('student_id')->constrained('students');
            $table->text('submission_text')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('submitted_at');
            $table->integer('obtained_marks')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['submitted', 'late', 'graded']);
            $table->timestamps();
        });

        // 20. Progress Reports
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('academic_year_id')->constrained();
            $table->enum('term', ['term1', 'term2', 'term3', 'annual']);
            $table->json('subject_marks'); // Store subject-wise marks
            $table->decimal('total_marks', 10, 2);
            $table->decimal('percentage', 5, 2);
            $table->enum('grade', ['A+', 'A', 'B+', 'B', 'C', 'D', 'F']);
            $table->integer('rank')->nullable();
            $table->text('teacher_remarks')->nullable();
            $table->text('principal_remarks')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // 21. Certificate Management
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['transfer', 'character', 'achievement', 'bonafide']);
            $table->text('content'); // HTML template
            $table->json('variables')->nullable(); // Available variables
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('template_id')->constrained('certificate_templates');
            $table->morphs('issuable'); // For students, employees
            $table->date('issue_date');
            $table->text('content'); // Generated content
            $table->text('remarks')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->timestamps();
        });

        // 22. Audit & Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('action');
            $table->string('module');
            $table->morphs('loggable');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // 23. Settings & Configurations
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('school_code');
            $table->string('affiliation_number')->nullable();
            $table->string('board');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->json('social_links')->nullable();
            $table->json('academic_settings')->nullable(); // Grading scale, etc.
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['text', 'number', 'boolean', 'json', 'file']);
            $table->string('group');
            $table->timestamps();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue');
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');

            $table->index(['queue', 'reserved_at', 'available_at']);
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down()
    {
        // Drop tables in reverse order
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('progress_reports');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework');
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('salary_templates');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_categories');
        Schema::dropIfExists('events');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostels');
        Schema::dropIfExists('student_transport');
        Schema::dropIfExists('route_vehicles');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('book_issues');
        Schema::dropIfExists('books');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('student_fees');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_categories');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_settings');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('student_parents');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('students');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};