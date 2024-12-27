<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Menambahkan pengecekan jika tabel belum ada
        if (!Schema::hasTable('tabel_data')) {
            Schema::create('tabel_data', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('idGedung');
                $table->float('suhu_ruang');
                $table->float('suhu_rak');
                $table->float('kelembapan_ruang');
                $table->float('kelembapan_rak');
                $table->smallInteger('fan_1')->default(0);  // Mengubah tipe data menjadi integer
                $table->smallInteger('fan_2')->default(0);  // Mengubah tipe data menjadi integer
                $table->timestamps();

                // Mendefinisikan foreign key
                $table->foreign('idGedung')->references('id')->on('tabel_gedung')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tabel_data');
    }
};