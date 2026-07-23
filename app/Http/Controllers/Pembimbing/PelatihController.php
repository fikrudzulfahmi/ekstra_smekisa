<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Pelatih;
use App\Models\User;
use App\Models\Pembimbing;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PelatihController extends Controller
{
    private function pembimbingAktif()
    {
        return Pembimbing::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        $pembimbing = $this->pembimbingAktif();
        $daftarEkstra = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->orderBy('nama')->get();
        $ekstraIds = $daftarEkstra->pluck('id')->toArray();

        // Hanya tampilkan pelatih yang terhubung dengan ekstra yang dibimbing
        $pelatih = Pelatih::with(['user', 'ekstra'])
            ->whereHas('ekstra', function ($q) use ($ekstraIds) {
                $q->whereIn('ekstra.id', $ekstraIds);
            })
            ->latest()
            ->get();

        return inertia('Pembimbing/Pelatih/Index', [
            'pelatih' => $pelatih,
            'daftarEkstra' => $daftarEkstra,
        ]);
    }

    public function store(Request $request)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        $pembimbing = $this->pembimbingAktif();
        $ekstraIdsPembimbing = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->pluck('ekstra.id')->toArray();

        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'no_hp'     => 'nullable|string|max:20',
            'ekstra_ids'   => 'required|array',
            'ekstra_ids.*' => ['exists:ekstra,id', Rule::in($ekstraIdsPembimbing)],
        ], [
            'ekstra_ids.*.in' => 'Anda hanya dapat menambahkan pelatih untuk ekstrakurikuler yang Anda bimbing.'
        ]);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        if (! $tahunAktif) {
            return back()->with('error', 'Belum ada tahun pelajaran aktif. Minta Admin untuk mengatur tahun aktif.');
        }

        DB::transaction(function () use ($validated, $tahunAktif) {
            $user = User::create([
                'name'     => $validated['nama'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'pelatih',
            ]);

            $pelatih = Pelatih::create([
                'user_id' => $user->id,
                'nama'    => $validated['nama'],
                'no_hp'   => $validated['no_hp'] ?? null,
            ]);

            $syncData = [];
            foreach ($validated['ekstra_ids'] ?? [] as $ekstraId) {
                $syncData[$ekstraId] = ['tahun_pelajaran_id' => $tahunAktif->id];
            }
            $pelatih->ekstra()->sync($syncData);
        });

        return back()->with('success', 'Pelatih berhasil ditambahkan.');
    }

    public function update(Request $request, Pelatih $pelatih)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        $pembimbing = $this->pembimbingAktif();
        $ekstraIdsPembimbing = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->pluck('ekstra.id')->toArray();

        // Pastikan pelatih yang diedit memang melatih ekstra milik pembimbing ini
        $pelatihEkstraIds = $pelatih->ekstra()->pluck('ekstra.id')->toArray();
        if (empty(array_intersect($ekstraIdsPembimbing, $pelatihEkstraIds))) {
            abort(403, 'Anda tidak berhak mengedit pelatih ini.');
        }

        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($pelatih->user_id)],
            'password'  => 'nullable|string|min:6',
            'no_hp'     => 'nullable|string|max:20',
            'ekstra_ids'   => 'required|array',
            'ekstra_ids.*' => ['exists:ekstra,id', Rule::in($ekstraIdsPembimbing)],
        ], [
            'ekstra_ids.*.in' => 'Anda hanya dapat menambahkan pelatih untuk ekstrakurikuler yang Anda bimbing.'
        ]);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        DB::transaction(function () use ($validated, $pelatih, $tahunAktif, $pelatihEkstraIds, $ekstraIdsPembimbing) {
            $pelatih->user->update([
                'name'  => $validated['nama'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $pelatih->user->update(['password' => Hash::make($validated['password'])]);
            }

            $pelatih->update([
                'nama'  => $validated['nama'],
                'no_hp' => $validated['no_hp'] ?? null,
            ]);

            // Sync ekstra: hanya ubah ekstra yang jadi wewenang pembimbing.
            // Ekstra milik pembimbing lain (jika ada) biarkan saja.
            $ekstraLain = array_diff($pelatihEkstraIds, $ekstraIdsPembimbing);
            $ekstraBaru = $validated['ekstra_ids'] ?? [];
            
            $finalEkstraIds = array_merge($ekstraLain, $ekstraBaru);

            $syncData = [];
            foreach ($finalEkstraIds as $ekstraId) {
                $syncData[$ekstraId] = ['tahun_pelajaran_id' => $tahunAktif?->id];
            }
            $pelatih->ekstra()->sync($syncData);
        });

        return back()->with('success', 'Data pelatih berhasil diperbarui.');
    }

    public function destroy(Pelatih $pelatih)
    {
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        $pembimbing = $this->pembimbingAktif();
        $ekstraIdsPembimbing = $pembimbing->ekstra()
            ->wherePivot('tahun_pelajaran_id', $tahunAktif?->id)
            ->pluck('ekstra.id')->toArray();

        $pelatihEkstraIds = $pelatih->ekstra()->pluck('ekstra.id')->toArray();
        if (empty(array_intersect($ekstraIdsPembimbing, $pelatihEkstraIds))) {
            abort(403, 'Anda tidak berhak menghapus pelatih ini.');
        }

        DB::transaction(function () use ($pelatih) {
            $pelatih->ekstra()->detach();
            $user = $pelatih->user;
            $pelatih->delete();
            $user?->delete();
        });

        return back()->with('success', 'Pelatih berhasil dihapus.');
    }
}
