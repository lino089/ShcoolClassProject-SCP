<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Journal;
class ReportController extends Controller
{
    public function teachingJournals(Request $request){
        $user   = $request->user();
        if($user->role !== 'vice_principal'){
            return response()->json([
                'success' => false,
                'message' => 'Hanya Waka yang dapat mengakses rekap laporan ini.'
            ], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'teacher_id' => 'nullable|exists:users,id'
        ]);

        $query = Journal::with([
            'schedule.teacher:id,name,nis_nip',
            'schedule.subject:id,name',
            'schedule.classRoom:id,name',
            'substituteTeacher:id,name'
        ])->whereBetween('date', [$request->start_date, $request->end_date]);

        if($request->filled('teacher_id')){
            $query->whereHas('schedule', function ($q) use ($request){
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        $reports = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Rekapitulasi jurnal KBM berhasil diambil.',
            'data' => $reports
        ], 200);
    }
}
