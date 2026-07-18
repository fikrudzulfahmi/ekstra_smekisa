<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelatih;
use App\Models\User;
use App\Models\Ekstra;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PelatihController extends Controller
{
    public function index()
    {
        return inertia('Admin/Pelatih/Index', [
            'pelatih' => Pelatih::with(['user', 'ekstra'])->latest()->get(),
            'daftarEkstra' => Ekstra::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'no_hp'     => 'nullable|string|max:20',
            'ekstra_ids'   => 'array',
            'ekstra_ids.*' => 'exists:ekstra,id',
        ]);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        if (! $tahunAktif) {
            return back()->with('error', 'Belum ada tahun pelajaran aktif. Set dulu tahun aktif.');
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

            // Assign ekstra (pivot) dengan tahun aktif
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
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($pelatih->user_id)],
            'password'  => 'nullable|string|min:6', // kosongkan jika tidak ganti password
            'no_hp'     => 'nullable|string|max:20',
            'ekstra_ids'   => 'array',
            'ekstra_ids.*' => 'exists:ekstra,id',
        ]);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        DB::transaction(function () use ($validated, $pelatih, $tahunAktif) {
            // Update user
            $pelatih->user->update([
                'name'  => $validated['nama'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $pelatih->user->update(['password' => Hash::make($validated['password'])]);
            }

            // Update pelatih
            $pelatih->update([
                'nama'  => $validated['nama'],
                'no_hp' => $validated['no_hp'] ?? null,
            ]);

            // Sync ekstra
            $syncData = [];
            foreach ($validated['ekstra_ids'] ?? [] as $ekstraId) {
                $syncData[$ekstraId] = ['tahun_pelajaran_id' => $tahunAktif?->id];
            }
            $pelatih->ekstra()->sync($syncData);
        });

        return back()->with('success', 'Data pelatih berhasil diperbarui.');
    }

    public function destroy(Pelatih $pelatih)
    {
        DB::transaction(function () use ($pelatih) {
            $pelatih->ekstra()->detach();
            $user = $pelatih->user;
            $pelatih->delete();
            $user?->delete(); // hapus akun login-nya juga
        });

        return back()->with('success', 'Pelatih berhasil dihapus.');
    }
}
