<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request){

        // Cek data yang harus masuk
        $validator = Validator::make($request->all(), [
            'nis_nip' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'required|string'
        ]);

        // Memastikan semua data terisi
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => [
                    'field' => $validator->errors()
                ]
            ]);
        }

        // Memvalidasi kredensial yang dimasukan
        if(!Auth::attempt($request->only('nis_nip', 'password'))){
            return response()->json([
                'success' => false,
                'message' => "Kredensial tidak valid",
                'error' => [
                    'field' => 'NIS/NIP atau Password salah'
                ]
            ]);
        }

        $user = User::where('nis_nip', $request->nis_nip)->firstOrFail();

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Login',
            "data" => $token
        ], 200);
    }

    public function me(Request $request){
        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            // Menggampil data user(pengguna yang sedang login), dan user() ini adalah method bawaan laravel bukan dari DB.
            'data' => $request->user() 
        ], 200);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil',
        ], 200);
    }
}
