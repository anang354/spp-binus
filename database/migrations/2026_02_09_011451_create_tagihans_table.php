<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kategori_biaya_id')->constrained('kategori_biayas')->onDelete('cascade');
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->date('jatuh_tempo');
            $table->integer('jumlah_tagihan');
            $table->integer('jumlah_diskon')->default(0);
            $table->integer('tagihan_netto');
            $table->string('nama_tagihan');
            $table->string('nama_diskon')->nullable();
            $table->string('status');
            $table->string('keterangan')->nullable();
            $table->string('jenis_tagihan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
