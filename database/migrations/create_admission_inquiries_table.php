<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_admission_inquiries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_number')->unique();
            $table->string('student_name');
            $table->date('student_dob');
            $table->enum('student_gender', ['male', 'female', 'other']);
            $table->string('class_applying_for');
            $table->string('parent_name');
            $table->string('parent_email');
            $table->string('parent_phone');
            $table->text('address');
            $table->enum('status', ['pending', 'contacted', 'follow_up', 'admitted', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('inquiry_number');
            $table->index('status');
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_inquiries');
    }
};