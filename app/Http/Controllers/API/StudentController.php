<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'nis' => 'required|string|unique:users,nis_nip',
            'class_id' =>'required|exists:classes,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = DB::transaction(function() use ($request){
            $randomPassword = Str::random(8);

            $student = User::create([
                'name' => $request->name,
                'role' => 'student',
                'nis_nip' => $request->nis,
                'password' => Hash::make($randomPassword),
                'must_change_password' => true,
                'class_id' => $request->class_id,
            ]);

            $parent = User::create([
                'name' => 'Wali dari '.$request->name,
                'role' => 'parent',
                'nis_nip' => "P-".$request->nis,
                'linked_student_id' => $student->id,
                'password' => Hash::make($randomPassword),
            ]);

            return [
                'student' => $student,
                'generate_password' => $randomPassword
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $result
        ], 201);
    }

    public function attendanceSummary(Request $request, $id){
        $targetStudent = User::where('role', 'student')->findOrFail($id);
        $currentUser = $request->user();

        if($currentUser->role === 'student' && $currentUser->id != $id){
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ],403);
        }

        if($currentUser->role === 'parent' && $currentUser->linked_student_id != $id){
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $summary = Attendance::join('journals', 'attendances.journal_id', '=', 'journals.id')
            ->where('attendances.student_id', $id)
            ->where('journals.status', 'completed')
            ->selectRaw("
                count(attendances.id) as total_meetings,
                count(case when attendances.status = 'Hadir' then 1 end) as total_hadir,
                count(case when attendances.status = 'Sakit' then 1 end) as total_sakit,
                count(case when attendances.status = 'Izin' then 1 end) as total_izin,
                count(case when attendances.status = 'Alpa' then 1 end) as total_alpa   
            ")
            ->first();
        
        $totalMeetings = (int)$summary->total_meetings;
        $totalHadir = (int)$summary->total_hadir;
        $attendancePercentage = $totalMeetings > 0 ? round($totalHadir / $totalMeetings * 100, 2) : 100.0;

        $attendanceSummary = [
            'student_id' => $targetStudent->id,
            'student_name' => $targetStudent->name,
            'total_meetings' => $totalMeetings,
            'breakdown' => [
                'hadir' => $totalHadir,
                'sakit' => (int) $summary->total_sakit,
                'izin' => (int) $summary->total_izin,
                'alpa' => (int) $summary->total_alpa,
            ],
            'attendances_percentage' => $attendancePercentage,
            'alpa_warning' => ((int) $summary->total_alpa >= 3)
        ];

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan presensi siswa berhasil diambil.',
            'data' => $attendanceSummary
        ], 200);
    }
}
