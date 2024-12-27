<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\dataModel;
use Illuminate\Http\Request;


class dataController extends Controller
{
    /**
     * Menangani data yang dikirim oleh ESP8266 (POST).
     */
    public function store(Request $request)
    {
        // Validasi input dari ESP8266
        $validated = $request->validate([
            'idGedung' => 'required|exists:tabel_gedung,id', // Validasi idGedung ada dalam tabel_gedung
            'suhu_ruang' => 'required|numeric',
            'suhu_rak' => 'required|numeric',
            'kelembapan_ruang' => 'required|numeric',
            'kelembapan_rak' => 'required|numeric',
            'fan_1' => 'required|integer|min:0|max:100',
            'fan_2' => 'required|integer|min:0|max:100',
        ]);

        // Simpan data yang diterima ke dalam tabel_data
        $data = dataModel::create([
            'idGedung' => $validated['idGedung'],
            'suhu_ruang' => $validated['suhu_ruang'],
            'suhu_rak' => $validated['suhu_rak'],
            'kelembapan_ruang' => $validated['kelembapan_ruang'],
            'kelembapan_rak' => $validated['kelembapan_rak'],
            'fan_1' => $validated['fan_1'],
            'fan_2' => $validated['fan_2'],
        ]);

        // Mengembalikan response jika berhasil
        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data' => $data
        ], 201);
    }

    /**
     * Mengambil data untuk ditampilkan di dashboard (GET).
     */
    public function index()
{
    // Ambil data terbaru berdasarkan id
    $data = dataModel::with('gedung') // Mengambil relasi dengan tabel gedung
                ->orderBy('id', 'desc') // Urutkan berdasarkan waktu terbaru
                ->take(25) // Ambil 25 data terbaru
                ->get();

    // Mengembalikan response dengan data untuk ditampilkan di dashboard
    return response()->json([
        'data' => $data
    ]);
}

public function getDataByGedung($idGedung)
{
    // Ambil data dari tabel_data berdasarkan idGedung yang diberikan
    $data = dataModel::with('gedung') // Mengambil relasi dengan tabel gedung
                ->where('idGedung', $idGedung) // Filter berdasarkan idGedung
                ->orderBy('updated_at', 'desc') // Urutkan berdasarkan ID terbaru
                ->take(25) // Ambil 25 data terbaru
                ->get();

    // Periksa apakah data ditemukan
    if ($data->isEmpty()) {
        return response()->json([
            'message' => 'Data tidak ditemukan untuk idGedung ini',
        ], 404);
    }

    // Mengembalikan response dengan data
    return response()->json([
        'data' => $data
    ]);
}


}
