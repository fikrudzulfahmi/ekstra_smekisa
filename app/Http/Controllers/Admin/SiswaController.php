<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ekstra;
use App\Models\TahunPelajaran;
use App\Services\SiswaSyncService;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        $query = Siswa::with(['kelas', 'ekstra'])
            ->where('tahun_pelajaran_id', $tahunAktif?->id);

        // Filter per kelas (opsional)
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        return inertia('Admin/Siswa/Index', [
            'siswa'        => $query->orderBy('nama')->get(),
            'daftarKelas'  => Kelas::orderBy('nama')->get(),
            'daftarEkstra' => Ekstra::orderBy('nama')->get(),
            'tahunAktif'   => $tahunAktif,
            'filters'      => $request->only('kelas_id'),
        ]);
    }

    public function sync(Request $request, SiswaSyncService $service)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        if (! $tahunAktif) {
            return back()->with('error', 'Belum ada tahun pelajaran aktif.');
        }

        $kelas = Kelas::findOrFail($request->kelas_id);

        try {
            $dataApi = $service->fetchByKelas($kelas->nama);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        if (empty($dataApi)) {
            return back()->with('error', "Tidak ada data siswa untuk kelas {$kelas->nama} di server data induk.");
        }

        $jumlah = 0;
        foreach ($dataApi as $row) {
            // Normalisasi jenis kelamin → L / P
            $jk = null;
            if (!empty($row['jk'])) {
                $jkUpper = strtoupper($row['jk']);
                $jk = str_starts_with($jkUpper, 'L') ? 'L' : 'P'; // "Laki-Laki" -> L, "Perempuan" -> P
            }

            Siswa::updateOrCreate(
                [
                    'nisn'               => $row['nisn'],
                    'tahun_pelajaran_id' => $tahunAktif->id,
                ],
                [
                    'kelas_id' => $kelas->id,
                    'nis'      => $row['nis'] ?? null,
                    'nama'     => $row['nama'],
                    'jk'       => $jk, // sudah jadi "L" atau "P"
                ]
            );
            $jumlah++;
        }


        return back()->with('success', "Berhasil sinkronisasi {$jumlah} siswa dari kelas {$kelas->nama}.");
    }

    // Assign / ubah ekstra untuk seorang siswa
    public function setEkstra(Request $request, Siswa $siswa)
    {
        $request->validate([
            'ekstra_id' => 'nullable|exists:ekstra,id',
        ]);

        $siswa->update(['ekstra_id' => $request->ekstra_id]);

        return back()->with('success', 'Ekstra siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
