<?php

return [
    'name' => env('SCHOOL_NAME', 'Smart School ERP'),
    
    'boards' => [
        'CBSE',
        'ICSE',
        'IB',
        'State Board',
    ],
    
    'class_types' => [
        'Nursery',
        'Primary',
        'Middle',
        'High School',
        'Higher Secondary',
    ],
    
    'exam_types' => [
        'Unit Test',
        'Quarterly Exam',
        'Half Yearly Exam',
        'Annual Exam',
        'Pre-Board Exam',
    ],
    
    'fee_frequencies' => [
        'monthly',
        'quarterly',
        'half_yearly',
        'yearly',
        'one_time',
    ],
    
    'payment_methods' => [
        'cash',
        'card',
        'bank_transfer',
        'online',
        'cheque',
    ],
    
    'attendance_statuses' => [
        'present',
        'absent',
        'late',
        'half_day',
        'holiday',
    ],
    
    'leave_statuses' => [
        'pending',
        'approved',
        'rejected',
        'cancelled',
    ],
    
    'student_statuses' => [
        'active',
        'inactive',
        'transferred',
        'passed_out',
        'suspended',
    ],
    
    'employee_types' => [
        'teaching',
        'non_teaching',
        'administrative',
        'supporting',
    ],
    
    'blood_groups' => [
        'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-',
    ],
    
    'genders' => [
        'male',
        'female',
        'other',
    ],
    
    'default_settings' => [
        'enable_sms_notification' => true,
        'enable_email_notification' => true,
        'enable_push_notification' => false,
        'attendance_cutoff_time' => '09:30:00',
        'late_mark_threshold' => 15, // minutes
        'max_leave_days_per_month' => 2,
        'automatic_fee_calculation' => true,
        'enable_library_system' => true,
        'enable_transport_system' => true,
        'enable_hostel_system' => true,
    ],
];