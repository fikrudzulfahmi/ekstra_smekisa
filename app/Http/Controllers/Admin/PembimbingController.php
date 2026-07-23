<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembimbing;
use App\Models\User;
use App\Models\Ekstra;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PembimbingController extends Controller
{
    public function index()
    {
        return inertia('Admin/Pembimbing/Index', [
            'pembimbing' => Pembimbing::with(['user', 'ekstra'])->latest()->get(),
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
                'role'     => 'pembimbing',
            ]);

            $pembimbing = Pembimbing::create([
                'user_id' => $user->id,
                'nama'    => $validated['nama'],
                'no_hp'   => $validated['no_hp'] ?? null,
            ]);

            $syncData = [];
            foreach ($validated['ekstra_ids'] ?? [] as $ekstraId) {
                $syncData[$ekstraId] = ['tahun_pelajaran_id' => $tahunAktif->id];
            }
            $pembimbing->ekstra()->sync($syncData);
        });

        return back()->with('success', 'Pembimbing berhasil ditambahkan.');
    }

    public function update(Request $request, Pembimbing $pembimbing)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($pembimbing->user_id)],
            'password'  => 'nullable|string|min:6',
            'no_hp'     => 'nullable|string|max:20',
            'ekstra_ids'   => 'array',
            'ekstra_ids.*' => 'exists:ekstra,id',
        ]);

        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();

        DB::transaction(function () use ($validated, $pembimbing, $tahunAktif) {
            $pembimbing->user->update([
                'name'  => $validated['nama'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $pembimbing->user->update(['password' => Hash::make($validated['password'])]);
            }

            $pembimbing->update([
                'nama'  => $validated['nama'],
                'no_hp' => $validated['no_hp'] ?? null,
            ]);

            $syncData = [];
            foreach ($validated['ekstra_ids'] ?? [] as $ekstraId) {
                $syncData[$ekstraId] = ['tahun_pelajaran_id' => $tahunAktif?->id];
            }
            $pembimbing->ekstra()->sync($syncData);
        });

        return back()->with('success', 'Data pembimbing berhasil diperbarui.');
    }

    public function destroy(Pembimbing $pembimbing)
    {
        DB::transaction(function () use ($pembimbing) {
            $pembimbing->ekstra()->detach();
            $user = $pembimbing->user;
            $pembimbing->delete();
            $user?->delete();
        });

        return back()->with('success', 'Pembimbing berhasil dihapus.');
    }
}
