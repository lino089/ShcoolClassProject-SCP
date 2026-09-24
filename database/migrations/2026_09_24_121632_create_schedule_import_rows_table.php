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
        Schema::create('schedule_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('schedule_imports');
            $table->json('raw_data');
            $table->foreignId('matched_class_id')->constrained('classes');
            $table->foreignId('matched_teacher_id')->constrained('users');
            $table->foreignId('matched_room_id')->constrained('rooms');
            $table->foreignId('matched_subject_id')->nullable()->constrained('subjects');
            $table->integer('day_of_week');
            $table->integer('period_number');
            $table->integer('duration_periods');
            $table->enum('match_status', ['matched', 'unmatched', 'conflict', 'duplicate']);
            $table->boolean('room_source_conflict')->default(false);
            $table->boolean('resolved_by_waka')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_import_rows');
    }
};
