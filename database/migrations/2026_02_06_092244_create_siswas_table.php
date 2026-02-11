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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->string('nama_siswa');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->string('nama_wali')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nomor_hp')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
