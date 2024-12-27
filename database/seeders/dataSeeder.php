<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class dataSeeder extends Seeder
{
    public function run()
    {
        // Data untuk 50 entri
        $data = [];
        for ($i = 1; $i <= 50; $i++) {
            // Membuat waktu acak dengan interval beberapa menit
            $randomMinutes = rand(1, 10080); // 10080 menit = 7 hari
            $created_at = Carbon::now()->subMinutes($randomMinutes);
            $updated_at = $created_at->copy()->addMinutes(rand(1, 1440)); // Update antara 1 sampai 1440 menit (1 hari)

            $data[] = [
                'idGedung' => rand(5, 8), // Acak antara Gedung 1 sampai Gedung 4
                'suhu_ruang' => rand(22, 30) + rand(0, 99) / 100, // Suhu ruang antara 22°C sampai 30°C
                'suhu_rak' => rand(28, 35) + rand(0, 99) / 100, // Suhu rak antara 28°C sampai 35°C
                'kelembapan_ruang' => rand(50, 65) + rand(0, 99) / 100, // Kelembapan ruang antara 50% sampai 65%
                'kelembapan_rak' => rand(50, 65) + rand(0, 99) / 100, // Kelembapan rak antara 50% sampai 65%
                'fan_1' => rand(40, 100), // Kecepatan kipas 1 antara 40% sampai 100%
                'fan_2' => rand(40, 100), // Kecepatan kipas 2 antara 40% sampai 100%
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ];
        }

        DB::table('tabel_data')->insert($data);
    }
}