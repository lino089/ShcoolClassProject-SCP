<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class academicSettingController extends Controller
{
    public function updateMondayStatus(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'is_upacara' => 'required|boolean',
            'password' => 'required|string'
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json([
                "success" => false,
                'message' => 'Validasi gagal',
                'errors' => [
                    'password' => ['Password tidak valid.']
                ]
            ], 401);
        }

        DB::table('system_configurations')->where('id', 1)->update([
            'monday_is_upacara' => $request->is_upacara
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status upacara hari senin berhasil diperbarui.',
            'data' => null
        ]);
    }
}
