<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\gedungModel;
use App\Models\dataModel; // Ganti dengan nama model yang sesuai untuk tabel data terkait
use Illuminate\Http\Request;

class gedungController extends Controller
{
    // Fungsi untuk mendapatkan semua gedung
    public function getAllGedung()
    {
        $gedungList = gedungModel::all();

        return response()->json([
            'data' => $gedungList
        ]);
    }

    // Fungsi untuk menambahkan data gedung
    public function insertGedung(Request $request)
    {
        try {
            // Validasi data input
            $request->validate([
                'lokasiGedung' => 'required|string|max:255',
                'ipAddress' => 'required|ip',
            ]);

            // Buat instance baru untuk gedung
            $gedung = new gedungModel();
            $gedung->lokasiGedung = $request->input('lokasiGedung');
            $gedung->ipAddress = $request->input('ipAddress');
            $gedung->save();

            return response()->json([
                'message' => 'Data gedung berhasil ditambahkan.',
                'data' => $gedung
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan data gedung.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Fungsi untuk mengedit data gedung
    public function editGedung(Request $request, $id)
    {
        try {
            // Validasi data input
            $validatedData = $request->validate([
                'lokasiGedung' => 'required|string|max:255',
                'ipAddress' => 'required|ip',
            ]);

            // Cari gedung berdasarkan ID
            $gedung = gedungModel::findOrFail($id);

            // Update data gedung
            $gedung->lokasiGedung = $validatedData['lokasiGedung'];
            $gedung->ipAddress = $validatedData['ipAddress'];
            $gedung->save();

            return response()->json([
                'message' => 'Data gedung berhasil diperbarui.',
                'data' => $gedung
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui data gedung.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Fungsi untuk menghapus data gedung
    public function deleteGedung($id)
    {
        try {
            // Cari gedung berdasarkan ID
            $gedung = gedungModel::findOrFail($id);

            // Hapus data terkait di tabel_data (ganti dengan nama model yang sesuai)
            dataModel::where('idGedung', $id)->delete();

            // Hapus gedung
            $gedung->delete();

            return response()->json([
                'message' => 'Data gedung dan data terkait berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data gedung.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}