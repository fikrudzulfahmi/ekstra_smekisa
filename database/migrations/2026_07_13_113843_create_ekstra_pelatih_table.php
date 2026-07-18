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
        Schema::create('ekstra_pelatih', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstra_id')->constrained('ekstra')->cascadeOnDelete();
            $table->foreignId('pelatih_id')->constrained('pelatih')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekstra_pelatih');
    }
};
