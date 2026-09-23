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
        Schema::create('period_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('day_type', ['senin_upacara', 'senin_reguler', 'selasa', 'rabu_kamis', 'jumat']);
            $table->integer('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_templates');
    }
};
