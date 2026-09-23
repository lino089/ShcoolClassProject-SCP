<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('period_templates')->insert([
            [
                'day_type' => 'senin_upacara',
                'period_number' => 1,
                'start_time' => '08:00:00',
                'end_time' => '08:45:00'
            ],
            [
                'day_type' => 'senin_reguler',
                'period_number' => 1,
                'start_time' => '07:15',
                'end_time' => '08:00:00'
            ],
            [
                'day_type' => 'selasa',
                'period_number' => 1,
                'start_time' => '07:30:00',
                'end_type' => '08:15:00' 
            ],
            [
                'day_type' => 'rabu_kamis',
                'period_number' => 1,
                'start_time' => '07:15:00',
                'end_time' => '08:00:00'
            ],
        ]);
    }
}
