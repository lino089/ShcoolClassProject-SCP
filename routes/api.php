<?php

use App\Http\Controllers\API\academicSettingController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\JournalController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\ScheduleImportController;
use App\Http\Controllers\API\SchoolClassController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\StudentPermisionController;
use App\Http\Controllers\API\SubjectController;
use App\Http\Controllers\API\TeacherController;
use App\Models\StudentPermision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Backend SCP berhasil Merespon',
        'data' => [
            'waktu_sekarang' => now()->toDateTimeString()

        ]
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::middleware(['auth:sanctum', 'role:vice_principal'])->group(function () {
    Route::get('/waka-only', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Selamat datang waka kurikulum'
        ]);
    });

    Route::patch('/academic-settings/monday-status', [academicSettingController::class, 'updateMondayStatus']);

    Route::get('/classes', [SchoolClassController::class, 'index']);

    Route::post('/classes', [SchoolClassController::class, 'store']);

    Route::delete('/classes/{id}', [SchoolClassController::class, 'destroy']);

    Route::post('/students', [StudentController::class, 'store']);

    Route::post('/teachers', [TeacherController::class, 'store']);

    Route::post('/teachers/{id}/assignments', [TeacherController::class, 'assignRole']);

    Route::post('/schedules', [ScheduleController::class, 'store']);

    Route::post('/schedule-imports', [ScheduleImportController::class, 'store']);

    Route::get('/schedule-imports/{id}', [ScheduleImportController::class, 'show']);

    Route::post('/subject', [SubjectController::class, 'store']);

    Route::post('/schedule-imports/{id}/confirm', [ScheduleImportController::class, 'confirm']);
});

Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    Route::post('/journals', [JournalController::class, 'store']);

    Route::post('/journals/{id}/attendance', [AttendanceController::class, 'store']);

    Route::post('/journals/{id}/complete', [JournalController::class, 'complete']);


});

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::post('/permissions', [StudentPermisionController::class, 'store']);
});
