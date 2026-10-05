<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SubstituteAssignment;
use App\Models\TeacherAssignment;
use App\Models\Schedule;

class SubstituteAssignmentController extends Controller
{
    public function store(Request $request){
        $isPiket = TeacherAssignment::where('user_id', $request->user()->id)
            ->where('type', 'kesiswaan')
            ->exists();
        
        if(!$isPiket){
            return response()->json([
                'success' => false,
                'message' => 'Hanya Guru Kesiswaan yang memiliki hak akses ini.'
            ], 403);
        }

        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'substitute_teacher_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'reason' => 'nullable|string'
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);

        if($schedule->teacher_id === $request->substitute_teacher_id){
            return response()->json([
                'success' => false,
                'message' => 'Guru pengganti tidak boleh sama dengan guru utama jadwal.'
            ], 422);
        }

        $assignment = SubstituteAssignment::create([
            'schedule_id' => $schedule->id,
            'original_teacher_id' => $schedule->teacher_id,
            'substitute_teacher_id' => $request->substitute_teacher_id,
            'assigned_by' => $request->user()->id,
            'date' => $request->date,
            'reason' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guru pengganti berhasil ditugaskan.',
            'data' => $assignment
        ], 201);
    }
}
