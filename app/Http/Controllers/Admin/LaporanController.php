<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Ekstra;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        $query = Kegiatan::with(['ekstra', 'pelatih'])
            ->withCount([
                'presensi as hadir_count' => fn($q) => $q->where('status', 'hadir'),
                'presensi as izin_count'  => fn($q) => $q->where('status', 'izin'),
                'presensi as sakit_count' => fn($q) => $q->where('status', 'sakit'),
                'presensi as alpha_count' => fn($q) => $q->where('status', 'alpha'),
                'presensi as total_count',
            ])
            ->where('tahun_pelajaran_id', $tahunAktif?->id);

        // Filter per ekstra (opsional)
        if ($request->filled('ekstra_id')) {
            $query->where('ekstra_id', $request->ekstra_id);
        }

        $kegiatan = $query->orderBy('ekstra_id')->latest('tanggal')->get();

        // Ringkasan total keseluruhan (untuk kartu di atas)
        $ringkasan = [
            'total_kegiatan' => $kegiatan->count(),
            'total_hadir'    => $kegiatan->sum('hadir_count'),
            'total_izin'     => $kegiatan->sum('izin_count'),
            'total_sakit'    => $kegiatan->sum('sakit_count'),
            'total_alpha'    => $kegiatan->sum('alpha_count'),
        ];

        return inertia('Admin/Laporan/Index', [
            'kegiatan'     => $kegiatan,
            'daftarEkstra' => Ekstra::orderBy('nama')->get(),
            'tahunAktif'   => $tahunAktif,
            'filters'      => $request->only('ekstra_id'),
            'ringkasan'    => $ringkasan,
        ]);
    }

    public function perKelas(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        // Default rentang: awal bulan s/d hari ini
        $tglMulai   = $request->input('tgl_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->input('tgl_selesai', now()->format('Y-m-d'));

        $laporan = [];
        $namaKelas = null;

        if ($request->filled('kelas_id')) {
            $kelas = \App\Models\Kelas::find($request->kelas_id);
            $namaKelas = $kelas?->nama;

            // Ambil siswa di kelas ini (tahun aktif) + hitung presensi per status
            // dengan syarat kegiatan-nya berada dalam rentang tanggal
            $siswa = \App\Models\Siswa::with('ekstra')
                ->where('tahun_pelajaran_id', $tahunAktif?->id)
                ->where('kelas_id', $request->kelas_id)
                ->withCount([
                    'presensi as hadir_count' => fn($q) => $q->where('status', 'hadir')
                        ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])),
                    'presensi as izin_count' => fn($q) => $q->where('status', 'izin')
                        ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])),
                    'presensi as sakit_count' => fn($q) => $q->where('status', 'sakit')
                        ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])),
                    'presensi as alpha_count' => fn($q) => $q->where('status', 'alpha')
                        ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])),
                    'presensi as total_count' => fn($q) => $q
                        ->whereHas('kegiatan', fn($k) => $k->whereBetween('tanggal', [$tglMulai, $tglSelesai])),
                ])
                ->orderBy('nama')
                ->get();

            $laporan = $siswa;
        }

        return inertia('Admin/Laporan/PerKelas', [
            'laporan'      => $laporan,
            'daftarKelas'  => \App\Models\Kelas::orderBy('nama')->get(),
            'tahunAktif'   => $tahunAktif,
            'namaKelas'    => $namaKelas,
            'filters'      => [
                'kelas_id'    => $request->kelas_id,
                'tgl_mulai'   => $tglMulai,
                'tgl_selesai' => $tglSelesai,
            ],
        ]);
    }
}
