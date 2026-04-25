<?php
// database/migrations/xxxx_add_qualification_fields_to_user_profiles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'qualification')) {
                $table->string('qualification')->nullable()->after('medical_conditions');
            }
            if (!Schema::hasColumn('user_profiles', 'experience')) {
                $table->integer('experience_years')->nullable()->after('qualification');
            }
            if (!Schema::hasColumn('user_profiles', 'bio')) {
                $table->text('bio')->nullable()->after('experience_years');
            }
        });
    }

    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['qualification', 'experience_years', 'bio']);
        });
    }
};