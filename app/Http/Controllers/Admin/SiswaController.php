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

        // Pencarian (opsional)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        return inertia('Admin/Siswa/Index', [
            'siswa'        => $query->orderBy('nama')->get(),
            'daftarKelas'  => Kelas::orderBy('nama')->get(),
            'daftarEkstra' => Ekstra::orderBy('nama')->get(),
            'tahunAktif'   => $tahunAktif,
            'filters'      => $request->only('kelas_id', 'search'),
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
                $jk = str_starts_with($jkUpper, 'L') ? 'L' : 'P';
            }

            // Ambil bagian NIS sebelum tanda "/" (biasanya 5 digit pertama)
            $nisUtuh = $row['nis'] ?? '';
            $nisParts = explode('/', $nisUtuh);
            $nisSingkat = trim($nisParts[0]);

            // Jika NIS kosong setelah diparsing, skip (karena NIS dipakai untuk deteksi)
            if (empty($nisSingkat)) {
                continue;
            }

            activity()->withoutLogs(function () use ($nisSingkat, $tahunAktif, $kelas, $row, $jk) {
                Siswa::updateOrCreate(
                    [
                        'nis'                => $nisSingkat,
                        'tahun_pelajaran_id' => $tahunAktif->id,
                    ],
                    [
                        'kelas_id' => $kelas->id,
                        'nisn'     => $row['nisn'] ?? null,
                        'nama'     => $row['nama'],
                        'jk'       => $jk,
                    ]
                );
            });
            $jumlah++;
        }

        activity()
            ->performedOn($kelas)
            ->log("Sinkronisasi {$jumlah} siswa kelas {$kelas->nama}");

        return back()->with('success', "Berhasil sinkronisasi {$jumlah} siswa dari kelas {$kelas->nama}.");
    }

    // Assign / ubah ekstra untuk seorang siswa
    public function setEkstra(Request $request, Siswa $siswa)
    {
        $request->validate([
            'ekstra_id' => 'nullable|exists:ekstra,id',
        ]);

        activity()->withoutLogs(function () use ($siswa, $request) {
            $siswa->update(['ekstra_id' => $request->ekstra_id]);
        });

        return back()->with('success', 'Ekstra siswa berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis'       => 'required|string|max:50',
            'nisn'      => 'nullable|string|max:50',
            'nama'      => 'required|string|max:100',
            'jk'        => 'required|in:L,P',
            'kelas_id'  => 'required|exists:kelas,id',
            'ekstra_id' => 'nullable|exists:ekstra,id',
        ]);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        if (! $tahunAktif) {
            return back()->with('error', 'Belum ada tahun pelajaran aktif.');
        }

        $exists = Siswa::where('nis', $request->nis)
            ->where('tahun_pelajaran_id', $tahunAktif->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Siswa dengan NIS tersebut sudah ada di tahun ajaran aktif.');
        }

        Siswa::create([
            'nis'                => $request->nis,
            'nisn'               => $request->nisn,
            'nama'               => $request->nama,
            'jk'                 => $request->jk,
            'kelas_id'           => $request->kelas_id,
            'ekstra_id'          => $request->ekstra_id,
            'tahun_pelajaran_id' => $tahunAktif->id,
        ]);

        return back()->with('success', 'Berhasil menambahkan siswa secara manual.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
