<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstra;
use Illuminate\Http\Request;

class EkstraController extends Controller
{
    public function index()
    {
        return inertia('Admin/Ekstra/Index', [
            'ekstra' => Ekstra::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255|unique:ekstra,nama',
            'deskripsi' => 'nullable|string',
            'nominal_hr'=> 'nullable|numeric|min:0',
        ]);

        if (empty($validated['nominal_hr'])) {
            $validated['nominal_hr'] = 0;
        }

        Ekstra::create($validated);

        return back()->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function update(Request $request, Ekstra $ekstra)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255|unique:ekstra,nama,' . $ekstra->id,
            'deskripsi' => 'nullable|string',
            'nominal_hr'=> 'nullable|numeric|min:0',
        ]);

        if (empty($validated['nominal_hr'])) {
            $validated['nominal_hr'] = 0;
        }

        $ekstra->update($validated);

        return back()->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Ekstra $ekstra)
    {
        $ekstra->delete();

        return back()->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }
}
