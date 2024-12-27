<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class gedungSeeder extends Seeder
{
    public function run()
    {
        // Membuat 2 data gedung secara acak
        for ($i = 0; $i < 4; $i++) {
            // Membuat nama gedung acak (A-Z)
            $gedungName = 'Gedung ' . chr(rand(65, 90));  // chr(rand(65, 90)) menghasilkan huruf A-Z

            // Membuat IP address acak dalam rentang 192.168.1.1 - 192.168.1.254
            $ipAddress = '192.168.1.' . rand(1, 254); // IP address random antara 192.168.1.1 dan 192.168.1.254

            DB::table('tabel_gedung')->insert([
                'lokasiGedung' => $gedungName,
                'ipAddress' => $ipAddress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}