<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $guarded = [];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    public function ekstra()
    {
        return $this->belongsTo(Ekstra::class);
    }
    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }
}
