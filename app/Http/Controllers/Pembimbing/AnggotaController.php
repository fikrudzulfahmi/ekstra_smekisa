<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Pembimbing;
use App\Models\Siswa;
use App\Models\Ekstra;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    private function pembimbingAktif()
    {
        return Pembimbing::where('user_id', auth()->id())->firstOrFail();
    }

    public function index(Request $request)
    {
        $pembimbing = $this->pembimbingAktif();
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        // Ekstra yang dibimbing
        $daftarEkstra = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->orderBy('nama')->get();
        $ekstraIds = $daftarEkstra->pluck('id')->toArray();

        $query = Siswa::with(['kelas', 'ekstra'])
            ->where('tahun_pelajaran_id', $tahunAktif?->id);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'anggota_saya') {
                $query->whereIn('ekstra_id', $ekstraIds);
            } elseif ($request->status === 'belum_ikut') {
                $query->whereNull('ekstra_id');
            } elseif ($request->status === 'ekstra_lain') {
                $query->whereNotNull('ekstra_id')->whereNotIn('ekstra_id', $ekstraIds);
            }
        }

        return inertia('Pembimbing/Anggota/Index', [
            'siswa' => $query->orderBy('nama')->get(),
            'daftarKelas' => Kelas::orderBy('nama')->get(),
            'daftarEkstra' => $daftarEkstra, // Ekstra yang dibimbing
            'tahunAktif' => $tahunAktif,
            'filters' => $request->only(['kelas_id', 'status']),
            'pembimbingEkstraIds' => $ekstraIds,
        ]);
    }

    public function setEkstra(Request $request, Siswa $siswa)
    {
        $pembimbing = $this->pembimbingAktif();
        $tahunAktif = \App\Models\TahunPelajaran::where('is_aktif', true)->first();
        $ekstraIds = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->pluck('ekstra.id')->toArray();

        // Pastikan pembimbing ini hanya mengatur ekstra yang dia bimbing
        $request->validate([
            'ekstra_id' => 'nullable|exists:ekstra,id',
        ]);

        if ($request->ekstra_id !== null && !in_array($request->ekstra_id, $ekstraIds)) {
            return back()->with('error', 'Anda tidak bisa memasukkan siswa ke ekstra pembimbing lain.');
        }

        // Jika siswa sudah punya ekstra, dan ekstra itu BUKAN milik pembimbing ini, tolak
        if ($siswa->ekstra_id !== null && !in_array($siswa->ekstra_id, $ekstraIds)) {
            return back()->with('error', 'Siswa ini sudah diatur oleh pembimbing lain.');
        }

        activity()->withoutLogs(function () use ($siswa, $request) {
            $siswa->update(['ekstra_id' => $request->ekstra_id]);
        });

        return back()->with('success', 'Ekstra siswa berhasil diperbarui.');
    }
}
