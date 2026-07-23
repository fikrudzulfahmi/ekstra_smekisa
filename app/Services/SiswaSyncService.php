<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiswaSyncService
{
    /**
     * Ambil data siswa dari API data induk berdasarkan nama kelas (rombel).
     * 
     * @param string $namaKelas
     * @param int $delayInSeconds Jeda waktu dalam detik sebelum/sesudah request (default: 3)
     * @return array
     * @throws \Exception
     */
    public function fetchByKelas(string $namaKelas, int $delayInSeconds = 3): array
    {
        // 1. Beri jeda aman sebelum menembak API agar tidak disangka spamming / brute-force
        if ($delayInSeconds > 0) {
            sleep($delayInSeconds);
        }

        // 2. Lakukan HTTP Request dengan proteksi penuh
        $response = Http::withHeaders([
            'X-API-KEY'  => config('services.data_induk.key'),
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept'     => 'application/json',
        ])
            ->timeout(30) // Batasi waktu tunggu maksimal 30 detik agar server Dewaweb tidak timeout
            ->get(config('services.data_induk.url'), [
                'kelas' => $namaKelas,
            ]);

        // 3. Cek jika request gagal
        if ($response->failed()) {
            $status = $response->status();
            $errorBody = $response->body();

            // Catat log error ke Laravel Log untuk mempermudah pelacakan di Dewaweb
            Log::error("API Data Induk Error [Kelas: {$namaKelas}] [Status: {$status}]: {$errorBody}");

            if ($status === 403) {
                throw new \Exception("Akses ditolak oleh Imunify360 (403). IP server Dewaweb Anda perlu di-whitelist oleh server target.");
            }

            if ($status === 401) {
                throw new \Exception("Gagal Akses (401). API Key 'X-API-KEY' salah atau tidak berlaku.");
            }

            throw new \Exception("Gagal mengambil data kelas {$namaKelas}. HTTP Status: {$status}");
        }

        // 4. Kembalikan data JSON
        $json = $response->json();
        return $json['data'] ?? [];
    }
}
