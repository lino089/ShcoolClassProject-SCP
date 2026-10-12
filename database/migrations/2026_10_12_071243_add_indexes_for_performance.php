<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['day_of_week', 'cycle_type']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['student_id', 'date']);
        });

        Schema::table('student_permisions', function (Blueprint $table) {
            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['day_of_week', 'cycle_type']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'date']);
        });

        Schema::table('student_permisions', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'status']);
        });
    }
};
