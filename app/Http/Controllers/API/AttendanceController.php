<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function store(Request $request, $id)
    {
        $journal = Journal::with('schedule')->findOrFail($id);
        $userId = $request->user()->id;

        //1. otorisasi
        if ($journal->schedule->teacher_id !== $userId && $journal->substitute_teacher_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        if ($journal->status !== 'ongoing') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi tidak dapat diubah karena sudah selesai. '
            ], 422);
        }

        // memvalidasi data input array sebelum disimpan ke database.
        $request->validate([
            'exceptions' => 'nullable|array',
            'exceptions.*.student_id' => 'required|exists:users,id',
            'exceptions.*.status' => 'required|in:Sakit,Izin,Alpa'
        ]);

        // 2. masukan data siswa bermasalah (Sakit/Izin/Alpa)
        DB::transaction(function () use ($journal, $request) {
            Attendance::where('journal_id', $journal->id)->delete();

            $exceptions = collect($request->input('exceptions', []));
            $exceptionsStudentId = $exceptions->pluck('student_id')->toArray();

            $allStudentIds = User::where('role', 'student')
                ->where('class_id', $journal->schedule->class_id)
                ->pluck('id');

            $attendanceData = [];
            $now = now();

            foreach ($exceptions as $exc) {
                $attendanceData[] = [
                    'journal_id' => $journal->id,
                    'student_id' => $exc['student_id'],
                    'status' => $exc['status'],
                    'date' => $journal->date,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            //3. masukan sisa siswa hadir
            $presentStudentIds = $allStudentIds->diff($exceptionsStudentId);
            foreach ($presentStudentIds as $studentId) {
                $attendanceData[] = [
                    'journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'date' => $journal->date,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            
            // 4. Eksekusi Bulk Insert
            if(!empty($attendanceData)){
                Attendance::insert($attendanceData);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disimpan'
        ], 200);


    }
}
