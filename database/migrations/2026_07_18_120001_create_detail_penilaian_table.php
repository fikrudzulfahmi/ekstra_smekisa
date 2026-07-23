<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaians')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai'); // 0 - 100
            $table->timestamps();

            $table->unique(['penilaian_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penilaian');
    }
};
