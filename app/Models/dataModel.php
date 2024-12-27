<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dataModel extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dari nama model yang di-plural-kan
    protected $table = 'tabel_data';

    // Tentukan kolom yang dapat diisi (mass assignment)
    protected $fillable = [
        'idGedung',
        'suhu_ruang',
        'suhu_rak',
        'kelembapan_ruang',
        'kelembapan_rak',
        'fan_1',
        'fan_2',
    ];

    // Relasi dengan tabel Gedung
    public function gedung()
    {
        return $this->belongsTo(gedungModel::class, 'idGedung');
    }
}