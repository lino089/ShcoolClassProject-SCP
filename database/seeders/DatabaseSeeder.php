<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::create([
        //     'role' => 'vice_principal',
        //     'nis_nip' => '12345678',
        //     'password' => Hash::make('password'),
        //     'must_change_password' => false,
        //     'name' => 'Waka Kurikulum',
        // ]);

        // DB::table('system_configurations')->insert([
        //     'id' => 1,
        //     'active_cycle' => 1,
        //     'monday_is_upacara' => true
        // ]);

        $now = now();

        DB::table('rooms')->insert([
            ['name' => 'Ruang Teori 1', 'capacity' => 36, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ruang Teori 2', 'capacity' => 36, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Lab Komputer 1', 'capacity' => 36, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Lab Komputer 2', 'capacity' => 36, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Lab IPA', 'capacity' => 36, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('subjects')->insert([
            ['name' => 'Matematika', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bahasa Indonesia', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bahasa Inggris', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pendidikan Agama', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pemrograman Web', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Basis Data', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pemrograman Berorientasi Objek', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
