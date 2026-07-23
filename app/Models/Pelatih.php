<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Pelatih extends Model
{
    use LogsActivity;

    protected $table = 'pelatih';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ekstra()
    {
        return $this->belongsToMany(Ekstra::class, 'ekstra_pelatih')
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
