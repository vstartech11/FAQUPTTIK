<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QnA;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
class ChatbotController extends Controller
{
// insertqna
public function insertQnA(Request $request)
{
    $validated = $request->validate([
        'question' => 'required|string',
        'answer' => 'required|string',
    ]);

    try {
        $qna = QnA::create($validated);
        return response()->json(['message' => 'Data QnA berhasil ditambahkan.', 'data' => $qna]);
    } catch (\Exception $e) {
        Log::error("Error: ".$e->getMessage());
        return response()->json(['message' => 'Terjadi kesalahan saat menambahkan data QnA.'], 500);
    }
}

// updateqna
public function editQnA(Request $request, $id)
{
    $validated = $request->validate([   
        'question' => 'required|string',
        'answer' => 'required|string',
    ]);

    try {
        $qna = QnA::find($id);
        if (!$qna) {
            return response()->json(['message' => 'Data QnA tidak ditemukan.'], 404);
        }

        $qna->question = $validated['question'];
        $qna->answer = $validated['answer'];
        $qna->save();

        return response()->json(['message' => 'Data QnA berhasil diperbarui.', 'data' => $qna]);
    } catch (\Exception $e) {
        Log::error("Error: ".$e->getMessage());
        return response()->json(['message' => 'Terjadi kesalahan saat memperbarui data QnA.'], 500);
    }
}

// deleteqna
public function deleteQnA($id)
{
    try {
        $qna = QnA::find($id);
        if (!$qna) {
            return response()->json(['message' => 'Data QnA tidak ditemukan.'], 404);
        }

        $qna->delete();
        return response()->json(['message' => 'Data QnA berhasil dihapus.']);
    } catch (\Exception $e) {
        Log::error("Error: ".$e->getMessage());
        return response()->json(['message' => 'Terjadi kesalahan saat menghapus data QnA.'], 500);
    }
}

//getqna
public function getQnA()
{
    try {
        $qna = QnA::all();
        Log::debug("QnA: ".$qna);
        if ($qna->isEmpty()) {
            return response()->json(['message' => 'Database QnA kosong.']);
        }
        return response()->json($qna);
    } catch (\Exception $e) {
        Log::error("Error: ".$e->getMessage());
        return response()->json(['message' => 'Terjadi kesalahan saat mengambil data QnA.'], 500);
    }
}

public function handleMessage(Request $request)
{
    try {
        // Waktu mulai eksekusi
        $startTime = microtime(true);

        $userMessage = $request->input('message');
        Log::debug("User Message: ".$userMessage);

        // Ambil mode dari database tanpa default
        $mode = Setting::first()->mode;
        Log::debug("Mode: ".$mode);

        // Ambil data QnA
        $qna = QnA::all();
        if ($qna->isEmpty()) {
            return response()->json(['reply' => 'Database QnA kosong.']);
        }

        $edges = [];
        foreach ($qna as $item) {
            $similarity = $this->calculateSimilarity($userMessage, $item->question);
            Log::debug("Similarity for QnA ID ".$item->id.": ".$similarity);
            $edges[] = [
                'u' => 'user_message', // User message sebagai sumber
                'v' => $item->id, // ID QnA sebagai tujuan
                'weight' => $similarity
            ];
        }

        // Pilih algoritma berdasarkan mode
        if ($mode === 'kruskal') {
            $bestMatch = $this->kruskal($edges);
        } else { // Mode Dijkstra
            $bestMatch = $this->djikstra($edges, $qna);
        }

        $response = $bestMatch ? $qna->find($bestMatch)->answer : 'Maaf, saya tidak memahami pertanyaan Anda.';
        Log::debug("Response: ".$response);

        // Waktu selesai eksekusi
        $endTime = microtime(true);

        // Hitung waktu eksekusi
        $executionTime = round($endTime - $startTime, 4); // Waktu dalam detik (4 angka di belakang koma)

        // Tambahkan waktu eksekusi ke dalam respons
        return response()->json([
            'reply' => $response,
            'execution_time' => $executionTime . ' seconds'
        ]);

    } catch (\Exception $e) {
        Log::error("Error: ".$e->getMessage());
        return response()->json([
            'reply' => 'Terjadi kesalahan saat memproses permintaan.',
            'execution_time' => $executionTime . ' seconds'
        ], 500);
    }
}



    private function calculateSimilarity($text1, $text2)
    {
        // Hitung kemiripan teks menggunakan algoritma sederhana (contoh: Levenshtein Distance)
        $distance = levenshtein(strtolower($text1), strtolower($text2));
        $maxLength = max(strlen($text1), strlen($text2));
        return 1 - ($distance / $maxLength); // Skala 0 (tidak mirip) sampai 1 (sama persis)
    }

    private function kruskal($edges)
{
    // Urutkan edge berdasarkan bobot (kemiripan tertinggi dulu)
    usort($edges, function ($a, $b) {
        return $b['weight'] <=> $a['weight'];
    });

    // Cari edge dengan bobot tertinggi yang terhubung dengan user_message
    $bestMatch = null;
    $highestSimilarity = 0;

    foreach ($edges as $edge) {
        if ($edge['u'] === 'user_message' && $edge['weight'] > 0.5) {
            // Ambang batas kemiripan > 0.5
            // Pilih QnA dengan bobot kemiripan tertinggi
            if ($edge['weight'] > $highestSimilarity) {
                $highestSimilarity = $edge['weight'];
                $bestMatch = $edge['v']; // Menyimpan ID QnA yang relevan
            }
        }
    }

    return $bestMatch; // Mengembalikan ID QnA dengan kemiripan tertinggi
}


    private function djikstra($edges, $qna)
    {
        $dist = []; // Jarak minimum ke setiap node
        $visited = []; // Node yang telah dikunjungi

        // Gunakan ID khusus untuk "user_message"
        $userMessageNode = 'user_message';

        // Inisialisasi jarak awal untuk setiap ID QnA
        foreach ($qna as $item) {
            $dist[$item->id] = INF; // Jarak awal tak hingga
            $visited[$item->id] = false; // Node belum dikunjungi
        }

        // Tentukan nilai jarak untuk user_message
        $dist[$userMessageNode] = 0; // Jarak awal untuk pesan pengguna adalah 0
        $visited[$userMessageNode] = false; // Tambahkan user_message ke visited array

        while (true) {
            // Cari node dengan jarak terkecil yang belum dikunjungi
            $currentNode = null;
            $currentDist = INF;

            foreach ($dist as $node => $distance) {
                if (!$visited[$node] && $distance < $currentDist) {
                    $currentDist = $distance;
                    $currentNode = $node;
                }
            }

            if ($currentNode === null) {
                break; // Semua node telah dikunjungi atau tidak ada node yang dapat diakses
            }

            $visited[$currentNode] = true;

            // Perbarui jarak untuk semua tetangga node saat ini
            foreach ($edges as $edge) {
                // Pastikan untuk memeriksa node yang valid untuk 'user_message'
                if ($edge['u'] === $currentNode && !$visited[$edge['v']]) {
                    $altDist = $dist[$currentNode] + (1 - $edge['weight']); // Jarak alternatif
                    if ($altDist < $dist[$edge['v']]) {
                        $dist[$edge['v']] = $altDist; // Perbarui jarak jika lebih pendek
                    }
                }
            }
        }

        // Ambil edge dengan bobot tertinggi yang terhubung dengan user_message
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($edges as $edge) {
            // Ambil edge dengan kemiripan tertinggi
            if ($edge['weight'] > $highestSimilarity) {
                $highestSimilarity = $edge['weight'];
                $bestMatch = $edge['v']; // Mengambil ID QnA dengan similarity tertinggi
            }
        }

        return $bestMatch; // ID QnA dengan similarity tertinggi, atau null jika tidak ditemukan
    }





}