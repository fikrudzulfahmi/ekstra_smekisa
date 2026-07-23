<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Siswa extends Model
{
    use LogsActivity;

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
    public function detailPenilaian()
    {
        return $this->hasMany(DetailPenilaian::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
