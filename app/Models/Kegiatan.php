<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Kegiatan extends Model
{
    use LogsActivity;

    protected $table = 'kegiatan';
    protected $guarded = [];

    protected $casts = ['tanggal' => 'date:Y-m-d'];

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
