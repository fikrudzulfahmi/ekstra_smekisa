<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunPelajaranController extends Controller
{
    public function index()
    {
        return inertia('Admin/TahunPelajaran/Index', [
            'tahunPelajaran' => TahunPelajaran::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:tahun_pelajaran,nama',
        ]);

        TahunPelajaran::create([
            'nama'     => $validated['nama'],
            'is_aktif' => false,
        ]);

        return back()->with('success', 'Tahun pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, TahunPelajaran $tahunPelajaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:tahun_pelajaran,nama,' . $tahunPelajaran->id,
        ]);

        $tahunPelajaran->update(['nama' => $validated['nama']]);

        return back()->with('success', 'Tahun pelajaran berhasil diperbarui.');
    }

    // Set satu tahun sebagai aktif (yang lain otomatis non-aktif)
    public function setAktif(TahunPelajaran $tahunPelajaran)
    {
        DB::transaction(function () use ($tahunPelajaran) {
            TahunPelajaran::query()->update(['is_aktif' => false]);
            $tahunPelajaran->update(['is_aktif' => true]);
        });

        return back()->with('success', "Tahun pelajaran {$tahunPelajaran->nama} kini aktif.");
    }

    public function destroy(TahunPelajaran $tahunPelajaran)
    {
        if ($tahunPelajaran->is_aktif) {
            return back()->with('error', 'Tidak bisa menghapus tahun pelajaran yang sedang aktif.');
        }

        $tahunPelajaran->delete();

        return back()->with('success', 'Tahun pelajaran berhasil dihapus.');
    }
}
