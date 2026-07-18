<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SiswaSyncService
{
    /** * Ambil data siswa dari API data induk berdasarkan nama kelas (rombel). */
    public function fetchByKelas(string $namaKelas): array
    {
        $response = Http::withHeaders([
            'X-API-KEY' => config('services.data_induk.key'),
            // TAMBAHKAN USER-AGENT UNTUK MELEWATI IMUNIFY360
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])->get(config('services.data_induk.url'), [
            'kelas' => $namaKelas,
        ]);

        if ($response->failed()) {
            // Ambil body response jika diblokir untuk mempermudah debug
            if ($response->status() == 403) {
                throw new \Exception('Akses ditolak oleh Imunify360. Perlu Whitelist IP server Anda ke server target.');
            }
            throw new \Exception('Gagal mengambil data dari server data induk. Status: ' . $response->status());
        }

        $json = $response->json();
        return $json['data'] ?? [];
    }
}
