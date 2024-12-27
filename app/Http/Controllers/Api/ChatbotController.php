<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function handleMessage(Request $request)
    {
        $userMessage = $request->input('message');

        // Simulasi algoritma pencarian QnA
        $qna = [
            "Apa itu UPT TIK?" => "UPT TIK adalah Unit Pelaksana Teknis Teknologi Informasi dan Komunikasi.",
            "Bagaimana cara mengakses jaringan kampus?" => "Anda dapat mengakses jaringan kampus melalui Wi-Fi ITK.",
            "Apa layanan yang disediakan oleh UPT TIK?" => "Layanan mencakup jaringan, server, dan email."
        ];

        $response = "Maaf, saya tidak memahami pertanyaan Anda.";
        foreach ($qna as $question => $answer) {
            if (stripos($question, $userMessage) !== false) {
                $response = $answer;
                break;
            }
        }

        return response()->json(['reply' => $response]);
    }
}