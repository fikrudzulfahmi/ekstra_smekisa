<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';
    protected $guarded = [];

    protected $casts = ['tanggal' => 'date'];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }
    public function ekstra()
    {
        return $this->belongsTo(Ekstra::class);
    }
    public function pelatih()
    {
        return $this->belongsTo(Pelatih::class);
    }
    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }
}
