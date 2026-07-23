<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Ekstra;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Exports\LaporanPerKelasExport;
use App\Exports\RekapNilaiExport;
use App\Models\Penilaian;
use Maatwebsite\Excel\Facades\Excel;

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
        [$laporan, $namaKelas, $tglMulai, $tglSelesai, $tahunAktif] = $this->buildLaporanPerKelas($request);

        return inertia('Admin/Laporan/PerKelas', [
            'laporan'      => $laporan,
            'daftarKelas'  => Kelas::orderBy('nama')->get(),
            'tahunAktif'   => $tahunAktif,
            'namaKelas'    => $namaKelas,
            'filters'      => [
                'kelas_id'    => $request->kelas_id,
                'tgl_mulai'   => $tglMulai,
                'tgl_selesai' => $tglSelesai,
            ],
        ]);
    }

    public function exportPerKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);

        [$laporan, $namaKelas, $tglMulai, $tglSelesai, $tahunAktif] = $this->buildLaporanPerKelas($request);

        $namaFile = 'Laporan-' . str_replace(' ', '_', $kelas->nama) . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new LaporanPerKelasExport(
                $laporan,
                $kelas->nama,
                $tahunAktif?->nama,
                $tglMulai,
                $tglSelesai
            ),
            $namaFile
        );
    }

    /**
     * Logic bersama untuk membangun data laporan per kelas.
     * Dipakai oleh perKelas() (tampilan Vue) dan exportPerKelas() (Excel)
     * supaya datanya selalu identik.
     *
     * @return array{0: Collection, 1: ?string, 2: string, 3: string, 4: ?TahunPelajaran}
     */
    private function buildLaporanPerKelas(Request $request): array
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        // Default rentang: awal bulan s/d hari ini
        $tglMulai   = $request->input('tgl_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->input('tgl_selesai', now()->format('Y-m-d'));

        $laporan = collect();
        $namaKelas = null;

        if ($request->filled('kelas_id')) {
            $kelas = Kelas::find($request->kelas_id);
            $namaKelas = $kelas?->nama;

            // Ambil siswa di kelas ini (tahun aktif) + hitung presensi per status
            // dengan syarat kegiatan-nya berada dalam rentang tanggal
            $laporan = Siswa::with('ekstra')
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
        }

        return [$laporan, $namaKelas, $tglMulai, $tglSelesai, $tahunAktif];
    }

    // ==========================================
    // BAGIAN REKAP NILAI (ADMIN)
    // ==========================================

    public function nilaiIndex(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        $query = Penilaian::with(['ekstra', 'pelatih'])
            ->withCount('detail as jumlah_siswa')
            ->withAvg('detail as rata_rata', 'nilai')
            ->where('tahun_pelajaran_id', $tahunAktif?->id);

        if ($request->filled('ekstra_id')) {
            $query->where('ekstra_id', $request->ekstra_id);
        }

        return inertia('Admin/Laporan/NilaiIndex', [
            'penilaian' => $query->latest('tanggal')->get(),
            'daftarEkstra' => Ekstra::orderBy('nama')->get(),
            'filters' => $request->only('ekstra_id'),
        ]);
    }

    public function nilaiShow(Penilaian $penilaian)
    {
        $penilaian->load('ekstra', 'pelatih', 'detail.siswa.kelas');

        $grup = $penilaian->detail
            ->groupBy(fn($d) => $d->siswa->kelas?->nama ?? 'Tanpa Kelas')
            ->map(fn($group) => $group->map(fn($d) => [
                'nama' => $d->siswa->nama,
                'nilai' => $d->nilai,
            ])->values());

        return inertia('Admin/Laporan/NilaiShow', [
            'penilaian' => $penilaian,
            'nilaiGrup' => $grup,
        ]);
    }

    public function nilaiPerKelas(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        
        $laporan = collect();
        $namaKelas = null;

        if ($request->filled('kelas_id')) {
            $kelas = Kelas::find($request->kelas_id);
            $namaKelas = $kelas?->nama;
            
            $laporan = Siswa::with('ekstra')
                ->where('tahun_pelajaran_id', $tahunAktif?->id)
                ->where('kelas_id', $request->kelas_id)
                ->withAvg('detailPenilaian as rata_rata_nilai', 'nilai')
                ->orderBy('nama')
                ->get();
        }

        return inertia('Admin/Laporan/NilaiPerKelas', [
            'laporan'      => $laporan,
            'daftarKelas'  => Kelas::orderBy('nama')->get(),
            'tahunAktif'   => $tahunAktif,
            'namaKelas'    => $namaKelas,
            'filters'      => [
                'kelas_id'    => $request->kelas_id,
            ],
        ]);
    }

    public function exportNilaiPerKelas(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        
        $laporan = Siswa::with('ekstra')
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->where('kelas_id', $request->kelas_id)
            ->withAvg('detailPenilaian as rata_rata_nilai', 'nilai')
            ->orderBy('nama')
            ->get();

        $namaFile = 'RekapNilai-' . str_replace(' ', '_', $kelas->nama) . '.xlsx';

        return Excel::download(
            new RekapNilaiExport($laporan, $kelas->nama, $tahunAktif?->nama),
            $namaFile
        );
    }
}
