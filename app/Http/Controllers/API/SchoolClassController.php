<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Data kelas berhasil diambil.',
            'data' => $classes->items(),
            'meta' => [
                'current_page' => $classes->currentPage(),
                'per_page' => $classes->perPage(),
                'total' => $classes->total(),
                'last_page' => $classes->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'level' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $schoolClass = SchoolClass::create([
            'name' => $request->name,
            'level' => $request->level
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $schoolClass
        ],201);
    }

    public function destroy($id){
        $schoolClass = SchoolClass::findOrFail($id);

        $schoolClass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data kelas berhasil dihapus',
            'data' => $schoolClass
        ]);
    }
}
