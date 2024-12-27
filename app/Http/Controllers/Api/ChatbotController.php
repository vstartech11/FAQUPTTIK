<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QnA;

class ChatbotController extends Controller
{
    public function handleMessage(Request $request)
    {
        $userMessage = $request->input('message');

        // Ambil semua QnA dari database
        $qna = QnA::all();

        // Jika database kosong, kirim respons default
        if ($qna->isEmpty()) {
            return response()->json(['reply' => 'Database QnA kosong.']);
        }

        // Bangun graf berdasarkan kemiripan teks
        $edges = [];
        foreach ($qna as $item) {
            $similarity = $this->calculateSimilarity($userMessage, $item->question);
            $edges[] = [
                'u' => 'user_message',
                'v' => $item->id,
                'weight' => $similarity
            ];
        }

        // Jalankan algoritma Kruskal untuk mencari koneksi dengan bobot minimum
        $bestMatch = $this->kruskal($edges, $qna);

        // Jika ada kecocokan yang baik, kirimkan jawaban
        if ($bestMatch) {
            $response = $qna->find($bestMatch)->answer;
        } else {
            $response = 'Maaf, saya tidak memahami pertanyaan Anda.';
        }

        return response()->json(['reply' => $response]);
    }

    private function calculateSimilarity($text1, $text2)
    {
        // Hitung kemiripan teks menggunakan algoritma sederhana (contoh: Levenshtein Distance)
        $distance = levenshtein(strtolower($text1), strtolower($text2));
        $maxLength = max(strlen($text1), strlen($text2));
        return 1 - ($distance / $maxLength); // Skala 0 (tidak mirip) sampai 1 (sama persis)
    }

    private function kruskal($edges, $qna)
    {
        // Urutkan edge berdasarkan bobot (kemiripan tertinggi dulu)
        usort($edges, function ($a, $b) {
            return $b['weight'] <=> $a['weight'];
        });

        // Ambil edge dengan bobot tertinggi yang terhubung dengan user_message
        foreach ($edges as $edge) {
            if ($edge['u'] === 'user_message' && $edge['weight'] > 0.5) {
                // Ambang batas kemiripan > 0.5
                return $edge['v'];
            }
        }

        return null; // Tidak ada kecocokan
    }
}