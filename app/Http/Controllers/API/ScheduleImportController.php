<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\ScheduleImport;

class ScheduleImportController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'cycle_type' => 'required|integer|in:1,2',
            'file_kelas' => 'required|file|mimes:pdf|max:2048',
            'file_ruangan' => 'required|file|mimes:pdf|max:2048'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'error' => $validator->errors()
            ], 422);
        }

        // Menyimpan file fisik
        $pathKelas = $request->file('file_kelas')->store('imports/schedules');
        $pathRuangan = $request->file('file_ruangan')->store('imports/schedules');

        $import = ScheduleImport::create([
            'waka_id' => $request->user()->id,
            'cycle_type' => $request->cycle_type,
            'file_path_kelas' => $pathKelas,
            'file_path_ruangan' => $pathRuangan,
            'status' => 'uploaded'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File jadwal diunggah. Sedang memperoses...',
            'data' => $import
        ], 201);

    }
}
