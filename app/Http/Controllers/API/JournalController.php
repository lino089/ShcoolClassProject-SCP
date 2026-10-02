<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\Journal;
class JournalController extends Controller
{
        public function store(Request $request){
            $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
            ]);
            
            $schedule = Schedule::findOrFail($request->schedule_id);

            if($schedule->teacher_id !== $request->user()->id){
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.',
                    'errors' => [
                        'schedule' => ['Anda tidak memiliki akses ke jadwal ini.']
                    ]
                ]);
            }

            $today = now()->toDateString(); //Menghasilkan yyyy-mm-dd sesuai WIB

            // mengambil journal yang sudah ada, atau buat jika belum
            $journal = Journal::firstOrCreate(
                ['schedule_id' => $schedule->id, 'date' => $today],
                ['status' => 'ongoing']
            );

            return response()->json([
                'success' => true,
                'message' => 'Journal berhasil dibuka.',
                'data' => $journal
            ], 201);
        }  
}
