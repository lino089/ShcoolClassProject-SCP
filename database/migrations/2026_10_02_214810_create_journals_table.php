<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Unique;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules');
            $table->date('date');
            $table->text('topic_description')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['ongoing', 'completed', 'teacher_absent'])->default('ongoing');
            $table->foreignId('substitute_teacher_id')->nullable()->constrained('users');
            $table->Unique(['schedule_id', 'date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
