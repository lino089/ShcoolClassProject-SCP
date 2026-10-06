<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Contracts\Encryption\StringEncrypter;
use Illuminate\Http\Request;
use App\Models\Journal;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\SubstituteAssignment;
class JournalController extends Controller
{
        public function store(Request $request){
            $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
            ]);
            
            $schedule = Schedule::findOrFail($request->schedule_id);
            $today = now()->toDateString(); //Menghasilkan yyyy-mm-dd sesuai WIB
            $userId = $request->user()->id;

            $isOriginalTeacher = ($schedule->teacher_id === $userId);

            $isSubstituteTeacher = SubstituteAssignment::where('schedule_id', $schedule->id)
                ->where('substitute_teacher_id', $userId)
                ->whereDate('date', $today)
                ->exists();

            if(!$isOriginalTeacher && !$isSubstituteTeacher){
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk membuka jadwal ini.'
                ], 403);
            }

            // mengambil journal yang sudah ada, atau buat jika belum
            $journal = Journal::firstOrCreate(
                ['schedule_id' => $schedule->id, 'date' => $today],
                ['status' => 'ongoing', 'substitute_teacher_id' => $isSubstituteTeacher ? $userId : null]
            );

            return response()->json([
                'success' => true,
                'message' => 'Journal berhasil dibuka.',
                'data' => $journal
            ], 201);
        }  

        public function complete(Request $request, $id){
            $journal = Journal::with('schedule')->findOrFail($id);

            if($journal->schedule->teacher_id !== $request->user()->id && $journal->substitute_teacher_id !== $request->user()-> id){
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            if ($journal->status !== 'ongoing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Jurnal sudah diselesaikan sebelumnya.'
                ], 422);
            }

            $request->validate([
                'topic_description' => 'required|string|min:10',
                'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240'
            ]);

            $file = $request->file('photo');
            $img = Image::decode($file);

            $img->scaleDown(width: 1024);

            $encode = $img->encodeUsingFileExtension('webp', quality: 75);

            $filename = 'journals/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($filename, (string) $encode);

            $journal->update([
                'topic_description' => $request->topic_description,
                'photo_path' => $filename,
                'status' => 'completed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jurnal KBM berhasil diselesaikan.',
                'data' => $journal
            ], 200);
        }
}
