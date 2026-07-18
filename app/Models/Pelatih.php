<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelatih extends Model
{
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
}
