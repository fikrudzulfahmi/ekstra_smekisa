<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Pembimbing extends Model
{
    use LogsActivity;

    protected $table = 'pembimbing';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ekstra()
    {
        return $this->belongsToMany(Ekstra::class, 'ekstra_pembimbing')
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
