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
        // SQLite: Mengubah kolom enum menjadi string agar fleksibel menampung 'pembimbing'
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('pelatih')->change();
        });

        Schema::create('pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->string('no_hp')->nullable();
            $table->timestamps();
        });

        Schema::create('ekstra_pembimbing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstra_id')->constrained('ekstra')->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->constrained('pembimbing')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekstra_pembimbing');
        Schema::dropIfExists('pembimbing');
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'pelatih'])->default('pelatih')->change();
        });
    }
};
