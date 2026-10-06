<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('substitute_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('original_teacher_id')->constrained('users');
            $table->foreignId('substitute_teacher_id')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->date('date');
            $table->text('reason')->nullable();
            $table->unique(['schedule_id', 'date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substitute_assignments');
    }
};
