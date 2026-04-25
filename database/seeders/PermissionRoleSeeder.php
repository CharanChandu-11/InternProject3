<?php
// database/seeders/PermissionRoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users', 'create users', 'edit users', 'delete users',
            'view students', 'create students', 'edit students', 'delete students',
            'view teachers', 'create teachers', 'edit teachers', 'delete teachers',
            'view parents', 'create parents', 'edit parents', 'delete parents',
            'view employees', 'create employees', 'edit employees', 'delete employees',
            
            // Academic Management
            'view classes', 'create classes', 'edit classes', 'delete classes',
            'view sections', 'create sections', 'edit sections', 'delete sections',
            'view subjects', 'create subjects', 'edit subjects', 'delete subjects',
            'view timetable', 'create timetable', 'edit timetable', 'delete timetable',
            
            // Attendance
            'view attendance', 'mark attendance', 'edit attendance', 'delete attendance',
            
            // Examinations
            'view exams', 'create exams', 'edit exams', 'delete exams',
            'view results', 'enter marks', 'publish results',
            
            // Fees
            'view fees', 'create fees', 'edit fees', 'delete fees',
            'collect fees', 'view payments', 'process refunds',
            
            // Library
            'view books', 'create books', 'edit books', 'delete books',
            'issue books', 'return books',
            
            // Transport
            'view routes', 'create routes', 'edit routes', 'delete routes',
            'view vehicles', 'create vehicles', 'edit vehicles', 'delete vehicles',
            
            // Hostel
            'view hostels', 'create hostels', 'edit hostels', 'delete hostels',
            'allocate rooms', 'deallocate rooms',
            
            // Communication
            'send notifications', 'view messages', 'send messages',
            'create announcements', 'view announcements',
            
            // Reports
            'view reports', 'generate reports', 'export reports',
            
            // Settings
            'manage settings', 'view settings', 'edit settings',
            'manage backup', 'restore backup',
            
            // Dashboard
            'view dashboard', 'view analytics',
            
            // Inventory
            'view inventory', 'create inventory', 'edit inventory', 'delete inventory',
            'manage stock',
            
            // HR
            'manage payroll', 'process salary',
            'manage leaves', 'approve leaves',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        
        // Super Admin - gets all permissions
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - gets most permissions except sensitive ones
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view students', 'create students', 'edit students',
            'view teachers', 'create teachers', 'edit teachers',
            'view parents', 'create parents', 'edit parents',
            'view employees', 'create employees', 'edit employees',
            'view classes', 'create classes', 'edit classes',
            'view sections', 'create sections', 'edit sections',
            'view subjects', 'create subjects', 'edit subjects',
            'view timetable', 'create timetable', 'edit timetable',
            'view attendance', 'mark attendance', 'edit attendance',
            'view exams', 'create exams', 'edit exams',
            'view results', 'enter marks', 'publish results',
            'view fees', 'create fees', 'edit fees', 'collect fees',
            'view payments',
            'view books', 'create books', 'edit books', 'issue books', 'return books',
            'view routes', 'create routes', 'edit routes',
            'view vehicles', 'create vehicles', 'edit vehicles',
            'view hostels', 'create hostels', 'edit hostels', 'allocate rooms',
            'send notifications', 'view messages', 'send messages',
            'create announcements', 'view announcements',
            'view reports', 'generate reports', 'export reports',
            'view dashboard', 'view analytics',
            'view inventory', 'create inventory', 'edit inventory', 'manage stock',
        ]);

        // Teacher Role
        $teacherRole = Role::create(['name' => 'teacher', 'guard_name' => 'web']);
        $teacherRole->givePermissionTo([
            'view students',
            'view classes',
            'view sections',
            'view subjects',
            'view timetable',
            'mark attendance',
            'view attendance',
            'view exams',
            'enter marks',
            'view results',
            'view messages',
            'send messages',
            'view announcements',
            'view dashboard',
        ]);

        // Student Role
        $studentRole = Role::create(['name' => 'student', 'guard_name' => 'web']);
        $studentRole->givePermissionTo([
            'view timetable',
            'view attendance',
            'view results',
            'view fees',
            'view books',
            'view messages',
            'view announcements',
            'view dashboard',
        ]);

        // Parent Role
        $parentRole = Role::create(['name' => 'parent', 'guard_name' => 'web']);
        $parentRole->givePermissionTo([
            'view attendance',
            'view results',
            'view fees',
            'view messages',
            'send messages',
            'view announcements',
            'view dashboard',
        ]);

        // Employee Role
        $employeeRole = Role::create(['name' => 'employee', 'guard_name' => 'web']);
        $employeeRole->givePermissionTo([
            'view attendance',
            'view messages',
            'send messages',
            'view announcements',
            'view dashboard',
        ]);

        $this->command->info('Permissions and roles created successfully!');
    }
}