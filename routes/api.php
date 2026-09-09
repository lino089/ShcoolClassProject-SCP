<?php

use App\Http\Controllers\API\academicSettingController;
use App\Http\Controllers\API\AuthController;
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
});
