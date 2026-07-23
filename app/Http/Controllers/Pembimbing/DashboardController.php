<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembimbing;
use App\Models\TahunPelajaran;

class DashboardController extends Controller
{
    private function pembimbingAktif()
    {
        return Pembimbing::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $pembimbing = $this->pembimbingAktif();
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        
        $jumlahEkstra = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->count();
        
        return inertia('Pembimbing/Dashboard', [
            'jumlahEkstra' => $jumlahEkstra,
        ]);
    }
}
