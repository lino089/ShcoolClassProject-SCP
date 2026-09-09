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
        Schema::create('cycle_history', function (Blueprint $table) {
            $table->id();
            $table->integer('cycle_number');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycle_history');
    }
};
