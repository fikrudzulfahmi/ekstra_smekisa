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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignId('ekstra_id')->constrained('ekstra')->cascadeOnDelete();
            $table->foreignId('pelatih_id')->constrained('pelatih')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('materi');
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable(); // ← TAMBAHKAN INI (menyimpan path foto)
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
