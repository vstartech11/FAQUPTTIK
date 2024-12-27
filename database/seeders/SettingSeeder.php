<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        // Tambahkan data awal jika belum ada
        Setting::firstOrCreate([
            'mode' => 'kruskal',
        ]);
    }
}