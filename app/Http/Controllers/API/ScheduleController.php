<?php

namespace App\Http\Controllers\API;

use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;

class ScheduleController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'cycle_type' => 'required|integer',
            'period_number' => 'required|integer',
            'duration_periods' => 'required|integer',
            'teacher_id' => 'required|integer|exists:users,id', 
            'class_id' => 'required|integer|exists:classes,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'day_of_week' => 'required|integer',
            'source' => 'required|in:manual,imported',
            'password' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        if(!Hash::check($request->password, $request->user()->password)){
            return response()->json([
                'success' => false,
                'message' => 'konfirmasi gagal',
                'errors' => [
                    'password' => ['Password Salah!!']
                ]
            ], 403);
        }

        $startPeriod = $request->period_number;
        $endPeriod = $startPeriod + $request->duration_periods - 1;

        $isConflict = Schedule::where('cycle_type', $request->cycle_type)
        ->where('day_of_week', $request->day_of_week)
        ->where(function ($query) use ($request){
            $query->where('teacher_id', $request->teacher_id)
                ->orWhere('class_id', $request->class_id)
                ->orWhere('room_id', $request->room_id);
        })
        ->where(function ($query) use ($startPeriod, $endPeriod){
            $query->where('period_number', '<=', $endPeriod)
                ->whereRaw('(period_number + duration_periods - 1) >= ?', [$startPeriod]);
        })->exists();

        if($isConflict){
            return response()->json([
                'success' => false,
                'message' => 'Jadwal bentrok!',
                'errors' => ['schedule' => ['Terjadi bentrok guru, kelas atau ruangan pada jam tersebut.']]
            ], 422);
        }

        $newSchedule = Schedule::create([
            'cycle_type' => $request->cycle_type,
            'period_number' => $request->period_number,
            'duration_periods' => $request->duration_periods,
            'teacher_id' => $request->teacher_id,
            'class_id' => $request->class_id,
            'room_id' => $request->room_id,
            'subject_id' => $request->subject_id,
            'day_of_week' => $request->day_of_week,
            'source' => $request->source
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dibuat',
            'data' => $newSchedule
        ], 201);
    }
}
