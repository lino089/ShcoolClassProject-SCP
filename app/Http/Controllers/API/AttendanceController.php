<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\StudentPermision;

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
            $journalDate = $journal->date;
            $classId = $journal->schedule->class_id;

            $approvedPermissions = StudentPermision::whereHas('student', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $journalDate)
                ->whereDate('end_date', '>=', $journalDate)
                ->get();

            $autoExceptedIds = [];
            $attendanceData = [];

            foreach ($approvedPermissions as $perm) {
                $autoExceptedIds[] = $perm->student_id;

                $status = ($perm->permission_type === 'sakit') ? 'Sakit' : 'Izin';

                $attendanceData[$perm->student_id] = [
                    'journal_id' => $journal->id,
                    'student_id' => $perm->student_id,
                    'status' => $status,
                    'date' => $journalDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $manualExceptions = collect($request->input('exceptions', []));
            foreach ($manualExceptions as $exc) {
                if (!in_array($exc['student_id'], $autoExceptedIds)) {
                    $attendanceData[$exc['student_id']] = [
                        'journal_id' => $journal->id,
                        'student_id' => $exc['student_id'],
                        'status' => $exc['status'],
                        'date' => $journalDate,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            $allStudentIds = User::where('role', 'student')->where('class_id', $classId)->pluck('id');
            $absentIds = array_keys($attendanceData);
            $presentStudentIds = $allStudentIds->diff($absentIds);

            foreach ($presentStudentIds as $studentId) {
                $attendanceData[] = [
                    'journal_id' => $journal->id,
                    'student_id' => $studentId,
                    'status' => 'Hadir',
                    'date' => $journalDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            Attendance::where('journal_id', $journal->id)->delete();
            Attendance::insert(array_values($attendanceData));

        });



        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disimpan'
        ], 200);


    }

}
