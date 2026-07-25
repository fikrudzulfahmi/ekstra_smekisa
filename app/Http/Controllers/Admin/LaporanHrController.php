<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Setting;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanHrController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $query = Kegiatan::with(['pelatih.user', 'ekstra'])
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('pelatih_id, ekstra_id, COUNT(DISTINCT tanggal) as jumlah_kegiatan')
            ->groupBy('pelatih_id', 'ekstra_id')
            ->get();

        $data = $query->map(function ($item) {
            $nominalHr = $item->ekstra->nominal_hr ?? 0;
            return [
                'id' => $item->pelatih_id . '_' . $item->ekstra_id,
                'nama_pelatih' => $item->pelatih->user->name ?? '-',
                'nama_ekstra' => $item->ekstra->nama ?? '-',
                'jumlah_kegiatan' => $item->jumlah_kegiatan,
                'nominal_hr' => $nominalHr,
                'total' => $item->jumlah_kegiatan * $nominalHr,
            ];
        })->sortBy('nama_pelatih')->values();

        return inertia('Admin/LaporanHr/Index', [
            'data' => $data,
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
            ],
        ]);
    }

    public function print(Request $request)
    {
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $tanggalCetak = $request->query('tanggal_cetak', Carbon::now()->format('Y-m-d'));
        $tanggalCetakIndo = Carbon::parse($tanggalCetak)->translatedFormat('d F Y');
        
        $tahunAktif = TahunPelajaran::where('is_aktif', true)->first();
        
        $query = Kegiatan::with(['pelatih.user', 'ekstra'])
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('pelatih_id, ekstra_id, COUNT(DISTINCT tanggal) as jumlah_kegiatan')
            ->groupBy('pelatih_id', 'ekstra_id')
            ->get();

        $data = $query->map(function ($item) {
            $nominalHr = $item->ekstra->nominal_hr ?? 0;
            return [
                'nama_pelatih' => $item->pelatih->user->name ?? '-',
                'nama_ekstra' => $item->ekstra->nama ?? '-',
                'jumlah_kegiatan' => $item->jumlah_kegiatan,
                'nominal_hr' => $nominalHr,
                'total' => $item->jumlah_kegiatan * $nominalHr,
            ];
        })->sortBy('nama_pelatih')->values();

        $settings = Setting::pluck('value', 'key')->toArray();

        return inertia('Admin/LaporanHr/Print', [
            'data' => $data,
            'periode' => Carbon::parse($start)->translatedFormat('d F Y') . ' - ' . Carbon::parse($end)->translatedFormat('d F Y'),
            'tahunPelajaran' => $tahunAktif ? $tahunAktif->nama : '-',
            'tanggalCetak' => $tanggalCetakIndo,
            'settings' => [
                'hr_signer_name' => $settings['hr_signer_name'] ?? 'Sugianto, S.Pd.I',
                'hr_signer_title' => $settings['hr_signer_title'] ?? 'Waka Kesiswaan',
            ]
        ]);
    }
}
