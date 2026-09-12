<?php

namespace App\Http\Controllers\API;

use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'nip' => 'required|string|unique:users,nis_nip',
            'fet_staff_id' => 'nullable|string|unique:users,fet_staff_id'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $randomPassword = Str::random(8);

        $teacher = User::create([
            'name' =>  $request->name,
            'role' => 'teacher',
            'nis_nip' => $request->nip,
            'fet_staff_id' => $request->fet_staff_id,
            'password' => Hash::make($randomPassword),
            'must_change_password' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun guru berhasil dibuat',
            'data' => [
                'teacher' => $teacher,
                'password' => $randomPassword
            ]
        ], 201);
    }
}
