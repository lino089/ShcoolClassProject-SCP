<?php

namespace App\Http\Controllers;

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
}
