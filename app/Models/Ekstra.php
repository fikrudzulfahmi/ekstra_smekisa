<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ekstra extends Model
{
    use LogsActivity;

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

    public function pelatih()
    {
        return $this->belongsToMany(Pelatih::class, 'ekstra_pelatih')
            ->withPivot('tahun_pelajaran_id')
            ->withTimestamps();
    }

    public function pembimbing()
    {
        return $this->belongsToMany(Pembimbing::class, 'ekstra_pembimbing')
            ->withPivot('tahun_pelajaran_id')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
