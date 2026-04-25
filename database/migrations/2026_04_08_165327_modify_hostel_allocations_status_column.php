<?php
// database/migrations/xxxx_modify_hostel_allocations_status_column.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For MySQL, modify the ENUM column
        Schema::table('hostel_allocations', function (Blueprint $table) {
            // Drop the old ENUM and recreate with proper values
            DB::statement("ALTER TABLE hostel_allocations MODIFY COLUMN status ENUM('pending', 'active', 'inactive', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        });
    }

    public function down()
    {
        Schema::table('hostel_allocations', function (Blueprint $table) {
            DB::statement("ALTER TABLE hostel_allocations MODIFY COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
        });
    }
};