<?php
// database/migrations/2024_01_01_000002_create_syllabus_topics_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('syllabus_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('week_number')->nullable();
            $table->integer('session_count')->default(1);
            $table->text('learning_objectives')->nullable();
            $table->text('teaching_methods')->nullable();
            $table->text('assessment_methods')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('syllabus_topics');
    }
};