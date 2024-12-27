<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QnA;

class QnASeeder extends Seeder
{
    public function run()
    {
        QnA::insert([
            [
                'question' => 'Apa itu UPT TIK?',
                'answer' => 'UPT TIK adalah Unit Pelaksana Teknis Teknologi Informasi dan Komunikasi.',
            ],
            [
                'question' => 'Bagaimana cara mengakses jaringan kampus?',
                'answer' => 'Anda dapat mengakses jaringan kampus melalui Wi-Fi ITK.',
            ],
            [
                'question' => 'Apa layanan yang disediakan oleh UPT TIK?',
                'answer' => 'Layanan mencakup jaringan, server, dan email.',
            ],
        ]);
    }
}