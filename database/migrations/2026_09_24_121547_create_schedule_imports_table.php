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
        Schema::create('schedule_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waka_id')->constrained('users');
            $table->integer('cycle_type');
            $table->string('file_path_kelas');
            $table->string('file_path_ruangan')->nullable();
            $table->enum('status', ['uploading', 'parsing', 'preview_ready', 'confirmed', 'faild']);
            $table->string('parser_version')->default('v1.0');
            $table->string('created-at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_imports');
    }
};
