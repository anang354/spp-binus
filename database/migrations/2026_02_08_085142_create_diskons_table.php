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
        Schema::create('diskons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biaya_id')->constrained()->cascadeOnDelete();
            $table->string('nama_diskon');
            $table->float('persentase')->nullable();
            $table->integer('nominal')->nullable();
            $table->enum('tipe', ['persentase', 'nominal']);
            $table->string('keterangan')->nullable();
            $table->string('jenjang');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('diskon_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained();
            $table->foreignId('diskon_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskons');
        Schema::dropIfExists('diskon_siswa');
    }
};
