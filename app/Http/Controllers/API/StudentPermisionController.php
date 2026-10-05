<?php

namespace App\Http\Controllers\API;

use App\Models\StudentPermision;
use App\Models\TeacherAssignment;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentPermisionController extends Controller
{
    public function store(Request $request){
        $user = $request->user();
        $studentId = null;

        if($user->role === 'student'){
            $studentId = $user->id;
        } elseif($user->role === 'parent'){
            $studentId = $user->linked_student_id;
        }

        if(!$studentId){
            return response()->json([
                'success' => false,
                'message' => 'Hanya siswa atau orang tua yang sah yanng dapat meajukan perizinan.'
            ], 403);
        }

        $request->validate([
            'permission_type' => 'required|in:sakit,izin,dispen',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:5',
            'attachment' => 'required|image|max:5120'
        ]); 

        $path = $request->file('attachment')->store('permissions/attachment', 'public');

        $permission = StudentPermision::create([
            'student_id' => $studentId,
            'permission_type' => $request->permission_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment_path' => $path,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan perizinan berhasil dikirim.',
            'data' => $permission
        ], 201);
    }

    public function updateStatus(Request $request, $id){
        $isKesiswaan = TeacherAssignment::where('user_id', $request->user()->id)
            ->where('type', 'kesiswaan')
            ->exists();

        if(!$isKesiswaan){
            return response()->json([
                'success' => false,
                'message' => 'Hanya staf kesiswaan yang memiliki wewenang ini.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $permission = StudentPermision::findOrFail($id);

        if ($permission->status !== 'pending'){
            return response()->json([
                'success' => false,
                'message' => 'Status perizinan ini sudah diproses sebelumnya.'
            ], 422);
        }

        $permission->update([
            'status' => $request->status,
            'approved_by' => $request->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perizinan berhasil diperbarui.',
            'data' => $permission
        ], 200);
    }
}
