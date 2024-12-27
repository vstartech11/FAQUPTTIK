<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class gedungModel extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dari nama model yang di-plural-kan
    protected $table = 'tabel_gedung';

    // Tentukan kolom yang dapat diisi (mass assignment)
    protected $fillable = [
        'lokasiGedung',
        'ipAddress',
    ];

    // Relasi dengan tabel Data (jika ada)
    public function data()
    {
        return $this->hasMany(dataModel::class, 'idGedung');
    }
}