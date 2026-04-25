<?php
// database/migrations/2024_01_01_000003_create_syllabus_resources_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('syllabus_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('syllabus_topic_id')->constrained('syllabus_topics')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['book', 'article', 'video', 'website', 'document', 'other']);
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('syllabus_resources');
    }
};
