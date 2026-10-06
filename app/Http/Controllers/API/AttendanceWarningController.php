<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceWarningController extends Controller
{
    public function index(Request $request){
        $user = $request->user();

        $isWaka = ($user->role === 'vice_principal');
        $assignments = TeacherAssignment::where('user_id', $user->id)->pluck('type', 'class_id');
        $isKesiswaan = $assignments->contains('kesiswaan');

        if(!$isWaka && !$isKesiswaan && !$assignments->contains('wali_kelas')){
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $query = User::where('users.role', 'student')
            ->join('classes', 'users.class_id', '=', 'classes.id')
            ->join('attendances', 'users.id', '=', 'attendances.student_id')
            ->join('journals', 'attendances.journal_id', '=', 'journals.id')
            ->where('journals.status', 'completed');

        if(!$isWaka && !$isKesiswaan){
            $managedClassIds = TeacherAssignment::where('user_id', $user->id)
                ->where('type', 'wali_kelas')
                ->pluck('class_id');
            $query->whereIn('users.class_id', $managedClassIds);
        }

        $warnings = $query->selectRaw("
            users.id as student_id,
            users.name as student_name,
            users.nis_nip as nis,
            classes.name as class_name,
            count(case when attendances.status = 'Alpa' then 1 end) as total_alpa
        ")
        ->groupBy('users.id', 'users.name', 'users.nis_nip', 'classes.name')
        ->havingRaw("count(case when attendances.status = 'Alpa' then 1 end) >= 3")
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar peringatan alpa siswa berhasil diambil.',
            'data' => $warnings
        ], 200);
    }
}
