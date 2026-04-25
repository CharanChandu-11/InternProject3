<?php
// database/migrations/xxxx_make_parent_type_nullable_in_parents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parents', function (Blueprint $table) {
            // SQLite doesn't support modifying columns directly, so we need to recreate
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->string('parent_type')->nullable()->change();
            }
        });
        
        // For SQLite, we need to handle differently
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Create temporary table
            Schema::rename('parents', 'parents_temp');
            
            Schema::create('parents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('parent_type')->nullable(); // Made nullable
                $table->string('occupation')->nullable();
                $table->string('office_address')->nullable();
                $table->string('office_phone')->nullable();
                $table->timestamps();
            });
            
            // Copy data
            DB::statement('INSERT INTO parents (id, user_id, parent_type, occupation, office_address, office_phone, created_at, updated_at) 
                          SELECT id, user_id, parent_type, occupation, office_address, office_phone, created_at, updated_at FROM parents_temp');
            
            // Drop temp table
            Schema::drop('parents_temp');
        }
    }

    public function down()
    {
        // Reverse the changes
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            $table->string('parent_type')->nullable(false)->change();
        }
    }
};