<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstra;
use App\Models\Pelatih;
use App\Models\Siswa;
use App\Models\TahunPelajaran;

class DashboardController extends Controller
{
    public function index()
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        $totalEkstra = Ekstra::count();
        $totalPelatih = Pelatih::count();

        // Siswa stats hanya berdasarkan tahun pelajaran aktif
        $siswaQuery = Siswa::query();
        if ($tahunAktif) {
            $siswaQuery->where('tahun_pelajaran_id', $tahunAktif->id);
        }

        $siswaIkutEkstra = (clone $siswaQuery)->whereNotNull('ekstra_id')->count();
        $siswaBelumEkstra = (clone $siswaQuery)->whereNull('ekstra_id')->count();

        // Ambil 5 aktivitas terbaru untuk dashboard
        $activities = \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'description' => $activity->description,
                    'subject_type' => class_basename($activity->subject_type),
                    'subject_id' => $activity->subject_id,
                    'causer_name' => $activity->causer ? $activity->causer->name : 'System',
                    'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return inertia('Admin/Dashboard', [
            'totalEkstra' => $totalEkstra,
            'totalPelatih' => $totalPelatih,
            'siswaIkutEkstra' => $siswaIkutEkstra,
            'siswaBelumEkstra' => $siswaBelumEkstra,
            'recentActivities' => $activities,
        ]);
    }
}
