<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekstra extends Model
{
    protected $table = 'ekstra';
    protected $guarded = [];

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
    public function kegiatan()
    {
        return $this->hasMany(Kegiatan::class);
    }

    // Many-to-many: satu ekstra bisa banyak pelatih
    public function pelatih()
    {
        return $this->belongsToMany(Pelatih::class, 'ekstra_pelatih')
            ->withPivot('tahun_pelajaran_id')
            ->withTimestamps();
    }
}
