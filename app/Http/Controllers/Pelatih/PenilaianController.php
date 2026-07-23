<?php

namespace App\Http\Controllers\Pelatih;

use App\Http\Controllers\Controller;
use App\Models\DetailPenilaian;
use App\Models\Pelatih;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenilaianController extends Controller
{
    /**
     * Ambil data pelatih yang sedang login.
     */
    private function pelatihAktif(): Pelatih
    {
        return Pelatih::where('user_id', Auth::id())->firstOrFail();
    }

    public function index()
    {
        $pelatih = $this->pelatihAktif();

        $penilaian = Penilaian::with('ekstra')
            ->where('pelatih_id', $pelatih->id)
            ->withCount('detail as jumlah_siswa')
            ->withAvg('detail as rata_rata', 'nilai')
            ->latest('tanggal')
            ->get();

        return inertia('Pelatih/Penilaian/Index', [
            'penilaian' => $penilaian,
        ]);
    }

    public function create()
    {
        $pelatih = $this->pelatihAktif();
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        return inertia('Pelatih/Penilaian/Create', [
            'daftarEkstra' => $pelatih->ekstra()
                ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
                ->orderBy('nama')->get(),
            'today' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Endpoint AJAX: ambil siswa (dikelompokkan per kelas) untuk sebuah ekstra.
     * Dipakai oleh halaman Create & Edit untuk memuat daftar siswa yang dinilai.
     */
    public function siswa(Request $request)
    {
        $request->validate(['ekstra_id' => 'required|exists:ekstra,id']);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        $siswa = Siswa::with('kelas')
            ->where('ekstra_id', $request->ekstra_id)
            ->where('tahun_pelajaran_id', $tahunAktif?->id)
            ->orderBy('nama')
            ->get()
            ->groupBy(fn($s) => $s->kelas?->nama ?? 'Tanpa Kelas')
            ->map(fn($group) => $group->map(fn($s) => [
                'id' => $s->id,
                'nama' => $s->nama,
            ])->values());

        return response()->json($siswa);
    }

    public function store(Request $request)
    {
        $pelatih = $this->pelatihAktif();
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        $data = $request->validate([
            'ekstra_id' => 'required|exists:ekstra,id',
            'tanggal' => 'required|date',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nilai' => 'required|array|min:1',
            'nilai.*.siswa_id' => 'required|exists:siswa,id',
            'nilai.*.nilai' => 'required|integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($data, $pelatih, $tahunAktif) {
            $penilaian = Penilaian::create([
                'ekstra_id' => $data['ekstra_id'],
                'pelatih_id' => $pelatih->id,
                'tahun_pelajaran_id' => $tahunAktif?->id,
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'tanggal' => $data['tanggal'],
            ]);

            foreach ($data['nilai'] as $item) {
                DetailPenilaian::create([
                    'penilaian_id' => $penilaian->id,
                    'siswa_id' => $item['siswa_id'],
                    'nilai' => $item['nilai'],
                ]);
            }
        });

        return redirect()->route('pelatih.penilaian.index')
            ->with('success', 'Penilaian berhasil disimpan.');
    }

    public function edit(Penilaian $penilaian)
    {
        $this->pastikanMilikSendiri($penilaian);

        $penilaian->load('ekstra', 'detail.siswa.kelas');

        $grup = $penilaian->detail
            ->groupBy(fn($d) => $d->siswa->kelas?->nama ?? 'Tanpa Kelas')
            ->map(fn($group) => $group->map(fn($d) => [
                'id' => $d->id, // id baris detail_penilaian, dipakai saat update
                'siswa_id' => $d->siswa_id,
                'nama' => $d->siswa->nama,
                'nilai' => $d->nilai,
            ])->values());

        return inertia('Pelatih/Penilaian/Edit', [
            'penilaian' => $penilaian,
            'nilaiGrup' => $grup,
        ]);
    }

    public function update(Request $request, Penilaian $penilaian)
    {
        $this->pastikanMilikSendiri($penilaian);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nilai' => 'required|array|min:1',
            'nilai.*.id' => 'required|exists:detail_penilaian,id',
            'nilai.*.nilai' => 'required|integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($data, $penilaian) {
            $penilaian->update([
                'judul' => $data['judul'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'tanggal' => $data['tanggal'],
            ]);

            foreach ($data['nilai'] as $item) {
                DetailPenilaian::where('id', $item['id'])
                    ->where('penilaian_id', $penilaian->id) // guard: hanya boleh milik penilaian ini
                    ->update(['nilai' => $item['nilai']]);
            }
        });

        return redirect()->route('pelatih.penilaian.index')
            ->with('success', 'Penilaian berhasil diperbarui.');
    }

    public function destroy(Penilaian $penilaian)
    {
        $this->pastikanMilikSendiri($penilaian);

        $penilaian->delete(); // detail_penilaian ikut terhapus (cascadeOnDelete)

        return back()->with('success', 'Penilaian berhasil dihapus.');
    }

    /**
     * Guard sederhana: pelatih hanya boleh mengubah/menghapus penilaian miliknya sendiri.
     */
    private function pastikanMilikSendiri(Penilaian $penilaian): void
    {
        abort_unless($penilaian->pelatih_id === $this->pelatihAktif()->id, 403);
    }
}
