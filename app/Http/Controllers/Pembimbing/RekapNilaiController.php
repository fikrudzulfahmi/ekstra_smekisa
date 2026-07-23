<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Pembimbing;
use App\Models\Penilaian;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use App\Exports\RekapNilaiExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapNilaiController extends Controller
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

        $query = Penilaian::with(['ekstra', 'pelatih'])
            ->withCount('detail as jumlah_siswa')
            ->withAvg('detail as rata_rata', 'nilai')
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->whereIn('ekstra_id', $ekstraIds);

        if ($request->filled('ekstra_id') && in_array($request->ekstra_id, $ekstraIds)) {
            $query->where('ekstra_id', $request->ekstra_id);
        }

        return inertia('Pembimbing/RekapNilai/Index', [
            'penilaian' => $query->latest('tanggal')->get(),
            'daftarEkstra' => $daftarEkstra,
            'filters' => $request->only('ekstra_id'),
        ]);
    }

    public function show(Penilaian $penilaian)
    {
        $pembimbing = $this->pembimbingAktif();
        $tahunAktif = \App\Models\TahunPelajaran::where('is_aktif', true)->first();
        $ekstraIds = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->pluck('ekstra.id')->toArray();
        
        if (!in_array($penilaian->ekstra_id, $ekstraIds)) {
            abort(403);
        }

        $penilaian->load('ekstra', 'pelatih', 'detail.siswa.kelas');

        $grup = $penilaian->detail
            ->groupBy(fn($d) => $d->siswa->kelas?->nama ?? 'Tanpa Kelas')
            ->map(fn($group) => $group->map(fn($d) => [
                'nama' => $d->siswa->nama,
                'nilai' => $d->nilai,
            ])->values());

        return inertia('Pembimbing/RekapNilai/Show', [
            'penilaian' => $penilaian,
            'nilaiGrup' => $grup,
        ]);
    }

    public function perKelas(Request $request)
    {
        $pembimbing = $this->pembimbingAktif();
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

        return inertia('Pembimbing/RekapNilai/PerKelas', [
            'laporan'      => $laporan,
            'daftarKelas'  => Kelas::orderBy('nama')->get(),
            'tahunAktif'   => $tahunAktif,
            'namaKelas'    => $namaKelas,
            'filters'      => [
                'kelas_id'    => $request->kelas_id,
            ],
        ]);
    }

    public function exportPerKelas(Request $request)
    {
        $pembimbing = $this->pembimbingAktif();
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $kelas = Kelas::find($request->kelas_id);
        
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
