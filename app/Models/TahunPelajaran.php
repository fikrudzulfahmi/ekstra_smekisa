<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    protected $table = 'tahun_pelajaran';
    protected $guarded = [];

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
    public function kegiatan()
    {
        return $this->hasMany(Kegiatan::class);
    }
}
