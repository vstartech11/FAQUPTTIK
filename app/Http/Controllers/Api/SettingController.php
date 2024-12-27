<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function getMode()
    {
        $mode = Setting::first()->mode ?? 'kruskal'; // Default mode jika belum diset
        return response()->json(['mode' => $mode]);
    }

    public function setMode(Request $request)
    {
        $validated = $request->validate([
            'mode' => 'required|in:kruskal,dijkstra', // Validasi input hanya boleh kruskal atau dijkstra
        ]);

        $setting = Setting::firstOrCreate([]);
        $setting->mode = $validated['mode'];
        $setting->save();

        return response()->json(['message' => 'Mode berhasil diubah.', 'mode' => $setting->mode]);
    }
}