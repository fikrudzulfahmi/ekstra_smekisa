<?php

namespace App\Http\Controllers\Pelatih;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KegiatanController extends Controller
{
    // Helper: ambil pelatih yang sedang login
    private function pelatih()
    {
        return auth()->user()->pelatih;
    }

    // Helper keamanan: pastikan kegiatan milik pelatih yang login
    private function pastikanMilikPelatih(Kegiatan $kegiatan)
    {
        if ($kegiatan->pelatih_id !== $this->pelatih()?->id) {
            abort(403, 'Anda tidak berhak mengakses kegiatan ini.');
        }
    }


    private function tahunAktif()
    {
        return TahunPelajaran::where('is_aktif', true)->first();
    }

    // Tampilkan form buat kegiatan baru
    public function create()
    {
        $pelatih = $this->pelatih();
        $tahunAktif = $this->tahunAktif();

        // Ekstra yang dilatih oleh pelatih ini
        $daftarEkstra = $pelatih
            ? $pelatih->ekstra()->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)->get()
            : collect();

        return inertia('Pelatih/Kegiatan/Create', [
            'daftarEkstra' => $daftarEkstra,
            'tahunAktif'   => $tahunAktif,
            'today'        => now()->format('Y-m-d'),
        ]);
    }

    // API internal: ambil siswa peserta ekstra, dikelompokkan per kelas
    public function siswaByEkstra(Request $request)
    {
        $tahunAktif = $this->tahunAktif();

        $siswa = Siswa::with('kelas')
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->where('ekstra_id', $request->ekstra_id)
            ->orderBy('nama')
            ->get()
            ->groupBy(fn($s) => $s->kelas?->nama ?? 'Tanpa Kelas');

        return response()->json($siswa);
    }

    // Simpan kegiatan + presensi
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ekstra_id'          => 'required|exists:ekstra,id',
            'tanggal'            => 'required|date',
            'materi'             => 'required|string|max:255',
            'deskripsi'          => 'nullable|string',
            'foto'               => 'nullable|image|max:5120', // maks 5MB
            'presensi'           => 'required|array',
            'presensi.*.siswa_id' => 'required|exists:siswa,id',
            'presensi.*.status'  => 'required|in:hadir,izin,sakit,alpha',
        ]);

        $pelatih = $this->pelatih();
        $tahunAktif = $this->tahunAktif();

        // Simpan foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kegiatan', 'public');
        }

        DB::transaction(function () use ($validated, $pelatih, $tahunAktif, $fotoPath) {
            $kegiatan = Kegiatan::create([
                'tahun_pelajaran_id' => $tahunAktif->id,
                'ekstra_id'          => $validated['ekstra_id'],
                'pelatih_id'         => $pelatih->id,
                'tanggal'            => $validated['tanggal'],
                'materi'             => $validated['materi'],
                'deskripsi'          => $validated['deskripsi'] ?? null,
                'foto'               => $fotoPath,
            ]);

            foreach ($validated['presensi'] as $p) {
                $kegiatan->presensi()->create([
                    'siswa_id' => $p['siswa_id'],
                    'status'   => $p['status'],
                ]);
            }
        });

        return redirect()->route('pelatih.kegiatan.index')
            ->with('success', 'Kegiatan & presensi berhasil disimpan.');
    }


    // Daftar history kegiatan (sementara placeholder, kita isi nanti)
    public function index()
    {
        $pelatih = $this->pelatih();
        $tahunAktif = $this->tahunAktif();

        $kegiatan = Kegiatan::with(['ekstra'])
            ->withCount([
                'presensi as hadir_count' => fn($q) => $q->where('status', 'hadir'),
                'presensi as izin_count'  => fn($q) => $q->where('status', 'izin'),
                'presensi as sakit_count' => fn($q) => $q->where('status', 'sakit'),
                'presensi as alpha_count' => fn($q) => $q->where('status', 'alpha'),
                'presensi as total_count',
            ])
            ->where('pelatih_id', $pelatih?->id)
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->latest('tanggal')
            ->get();

        return inertia('Pelatih/Kegiatan/Index', [
            'kegiatan' => $kegiatan,
        ]);
    }


    // Tampilkan halaman edit kegiatan + presensi
    public function edit(Kegiatan $kegiatan)
    {
        // Proteksi: pastikan kegiatan ini milik pelatih yang login
        $this->pastikanMilikPelatih($kegiatan);

        $kegiatan->load(['ekstra', 'presensi.siswa.kelas']);

        // Kelompokkan presensi per kelas
        $presensiGrup = $kegiatan->presensi
            ->sortBy(fn($p) => $p->siswa->nama)
            ->groupBy(fn($p) => $p->siswa->kelas?->nama ?? 'Tanpa Kelas');

        // Rekap jumlah tiap status
        $rekap = [
            'hadir' => $kegiatan->presensi->where('status', 'hadir')->count(),
            'izin'  => $kegiatan->presensi->where('status', 'izin')->count(),
            'sakit' => $kegiatan->presensi->where('status', 'sakit')->count(),
            'alpha' => $kegiatan->presensi->where('status', 'alpha')->count(),
            'total' => $kegiatan->presensi->count(),
        ];

        return inertia('Pelatih/Kegiatan/Edit', [
            'kegiatan'     => $kegiatan,
            'presensiGrup' => $presensiGrup,
            'rekap'        => $rekap,
        ]);
    }

    // Simpan perubahan kegiatan + presensi
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $this->pastikanMilikPelatih($kegiatan);

        $validated = $request->validate([
            'tanggal'             => 'required|date',
            'materi'              => 'required|string|max:255',
            'deskripsi'           => 'nullable|string',
            'foto'                => 'nullable|image|max:5120',
            'presensi'            => 'required|array',
            'presensi.*.id'       => 'required|exists:presensi,id',   // ← id presensi
            'presensi.*.status'   => 'required|in:hadir,izin,sakit,alpha',
        ]);

        // Ganti foto jika ada upload baru
        $fotoPath = $kegiatan->foto;
        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('kegiatan', 'public');
        }

        DB::transaction(function () use ($validated, $kegiatan, $fotoPath) {
            $kegiatan->update([
                'tanggal'   => $validated['tanggal'],
                'materi'    => $validated['materi'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'foto'      => $fotoPath,
            ]);

            foreach ($validated['presensi'] as $p) {
                $kegiatan->presensi()
                    ->where('id', $p['id'])   // ← cocokkan dengan id presensi
                    ->update(['status' => $p['status']]);
            }
        });

        return redirect()->route('pelatih.kegiatan.index')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }


    // Hapus kegiatan
    public function destroy(Kegiatan $kegiatan)
    {
        $this->pastikanMilikPelatih($kegiatan);
        if ($kegiatan->foto) {
            Storage::disk('public')->delete($kegiatan->foto);
        }
        $kegiatan->delete();
        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }
}
