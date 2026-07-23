<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Pembimbing;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class RekapPresensiController extends Controller
{
    private function pembimbingAktif()
    {
        return Pembimbing::where('user_id', auth()->id())->firstOrFail();
    }

    public function index(Request $request)
    {
        $pembimbing = $this->pembimbingAktif();
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        
        $daftarEkstra = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->orderBy('nama')->get();
        $ekstraIds = $daftarEkstra->pluck('id')->toArray();

        $query = Kegiatan::with(['ekstra', 'pelatih'])
            ->withCount([
                'presensi as hadir_count' => fn($q) => $q->where('status', 'hadir'),
                'presensi as izin_count'  => fn($q) => $q->where('status', 'izin'),
                'presensi as sakit_count' => fn($q) => $q->where('status', 'sakit'),
                'presensi as alpha_count' => fn($q) => $q->where('status', 'alpha'),
                'presensi as total_count',
            ])
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->whereIn('ekstra_id', $ekstraIds);

        if ($request->filled('ekstra_id') && in_array($request->ekstra_id, $ekstraIds)) {
            $query->where('ekstra_id', $request->ekstra_id);
        }

        $kegiatan = $query->orderBy('ekstra_id')->latest('tanggal')->get();

        $ringkasan = [
            'total_kegiatan' => $kegiatan->count(),
            'total_hadir'    => $kegiatan->sum('hadir_count'),
            'total_izin'     => $kegiatan->sum('izin_count'),
            'total_sakit'    => $kegiatan->sum('sakit_count'),
            'total_alpha'    => $kegiatan->sum('alpha_count'),
        ];

        return inertia('Pembimbing/RekapPresensi/Index', [
            'kegiatan'     => $kegiatan,
            'daftarEkstra' => $daftarEkstra,
            'tahunAktif'   => $tahunAktif,
            'filters'      => $request->only('ekstra_id'),
            'ringkasan'    => $ringkasan,
        ]);
    }

    public function perKelas(Request $request)
    {
        $pembimbing = $this->pembimbingAktif();
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        
        $daftarEkstra = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->orderBy('nama')->get();
        $ekstraIds = $daftarEkstra->pluck('id')->toArray();

        $tglMulai   = $request->input('tgl_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->input('tgl_selesai', now()->format('Y-m-d'));

        $laporan = collect();
        $namaKelas = null;
        $namaEkstra = null;

        if ($request->filled('kelas_id') && $request->filled('ekstra_id')) {
            $kelas = Kelas::find($request->kelas_id);
            $namaKelas = $kelas?->nama;
            
            $ekstra = $daftarEkstra->firstWhere('id', $request->ekstra_id);
            $namaEkstra = $ekstra?->nama;

            if (in_array($request->ekstra_id, $ekstraIds)) {
                $laporan = Siswa::with('ekstra')
                    ->where('tahun_pelajaran_id', $tahunAktif?->id)
                    ->where('kelas_id', $request->kelas_id)
                    ->where('ekstra_id', $request->ekstra_id)
                    ->withCount([
                        'presensi as hadir_count' => fn($q) => $q->where('status', 'hadir')
                            ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])->where('ekstra_id', $request->ekstra_id)),
                        'presensi as izin_count' => fn($q) => $q->where('status', 'izin')
                            ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])->where('ekstra_id', $request->ekstra_id)),
                        'presensi as sakit_count' => fn($q) => $q->where('status', 'sakit')
                            ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])->where('ekstra_id', $request->ekstra_id)),
                        'presensi as alpha_count' => fn($q) => $q->where('status', 'alpha')
                            ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])->where('ekstra_id', $request->ekstra_id)),
                        'presensi as total_count' => fn($q) => $q
                            ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])->where('ekstra_id', $request->ekstra_id)),
                    ])
                    ->orderBy('nama')
                    ->get();
            }
        }

        return inertia('Pembimbing/RekapPresensi/PerKelas', [
            'laporan'      => $laporan,
            'daftarKelas'  => Kelas::orderBy('nama')->get(),
            'daftarEkstra' => $daftarEkstra,
            'tahunAktif'   => $tahunAktif,
            'namaKelas'    => $namaKelas,
            'namaEkstra'   => $namaEkstra,
            'filters'      => [
                'kelas_id'    => $request->kelas_id,
                'ekstra_id'   => $request->ekstra_id,
                'tgl_mulai'   => $tglMulai,
                'tgl_selesai' => $tglSelesai,
            ],
        ]);
    }
}
