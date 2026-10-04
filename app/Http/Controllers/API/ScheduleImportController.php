<?php

namespace App\Http\Controllers\API;

use App\Jobs\ParseSchedulePdfJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\ScheduleImport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Schedule;
use Illuminate\Support\Facades\Storage;
class ScheduleImportController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cycle_type' => 'required|integer|in:1,2',
            'file_kelas' => 'required|file|mimes:pdf|max:2048',
            'file_ruangan' => 'required|file|mimes:pdf|max:2048'
        ]);

        if ($validator->fails()) {
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

        ParseSchedulePdfJob::dispatch($import->id);

        return response()->json([
            'success' => true,
            'message' => 'File jadwal diunggah. Sedang memperoses...',
            'data' => $import
        ], 201);
    }

    public function show($id)
    {
        $import = ScheduleImport::with('rows')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail preview import jadwal.',
            'data' => $import
        ]);
    }

    public function confirm(Request $request, $id)
    {
        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password Salah.']);
        }

        $import = ScheduleImport::with('rows')->where('status', 'preview_ready')->findOrFail($id);

        DB::transaction(function () use ($import) {
            Schedule::where('cycle_type', $import->cycle_type)->delete();


            foreach ($import->rows as $row) {
                if ($row->match_status === 'matched') {
                    Schedule::create([
                        'teacher_id' => $row->matched_teacher_id,
                        'class_id' => $row->matched_class_id,
                        'room_id' => $row->matched_room_id,
                        'subject_id' => $row->matched_subject_id,
                        'day_of_week' => $row->day_of_week,
                        'period_number' => $row->period_number,
                        'duration_periods' => $row->duration_periods,
                        'cycle_type' => $import->cycle_type,
                        'source' => 'imported',
                        'import_batch_id' => $import->id
                    ]);
                }
            }
            Storage::delete([$import->file_path_kelas, $import->file_path_ruangan]);

            $import->update([
                'status' => 'confirmed',
                'file_path_kelas' => null,
                'file_path_ruangan' => null
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Import jadwal berhasil dikonfirmasi'
        ], 200);
    }
}
