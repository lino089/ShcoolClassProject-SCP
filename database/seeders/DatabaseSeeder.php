<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'role' => 'vice_principal',
            'nis_nip' => '12345678',
            'password' => Hash::make('password'),
            'must_change_password' => false,
            'name' => 'Waka Kurikulum',
        ]);
    }
}
