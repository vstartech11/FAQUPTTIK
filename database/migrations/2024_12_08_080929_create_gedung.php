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
        // Menambahkan kondisi ifNotExists untuk membuat tabel hanya jika belum ada
        if (!Schema::hasTable('tabel_gedung')) {
            Schema::create('tabel_gedung', function (Blueprint $table) {
                $table->id();
                $table->string('lokasiGedung');
                $table->string('ipAddress')->unique();
                $table->timestamps();
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
        Schema::dropIfExists('tabel_gedung');
    }
};