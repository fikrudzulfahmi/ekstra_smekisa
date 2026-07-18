<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\EkstraController;
use App\Http\Controllers\Admin\TahunPelajaranController;
use App\Http\Controllers\Admin\PelatihController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Pelatih\KegiatanController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Redirect setelah login → arahkan sesuai role
Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    return redirect()->route($role === 'admin' ? 'admin.dashboard' : 'pelatih.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grup route khusus Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Admin/Dashboard');
    })->name('dashboard');

    // Tahun Pelajaran
    Route::get('tahun-pelajaran', [TahunPelajaranController::class, 'index'])->name('tahun-pelajaran.index');
    Route::post('tahun-pelajaran', [TahunPelajaranController::class, 'store'])->name('tahun-pelajaran.store');
    Route::put('tahun-pelajaran/{tahunPelajaran}', [TahunPelajaranController::class, 'update'])->name('tahun-pelajaran.update');
    Route::patch('tahun-pelajaran/{tahunPelajaran}/aktif', [TahunPelajaranController::class, 'setAktif'])->name('tahun-pelajaran.aktif');
    Route::delete('tahun-pelajaran/{tahunPelajaran}', [TahunPelajaranController::class, 'destroy'])->name('tahun-pelajaran.destroy');

    // Kelas
    Route::get('kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::post('kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::put('kelas/{kelas}', [KelasController::class, 'update'])->name('kelas.update');
    Route::delete('kelas/{kelas}', [KelasController::class, 'destroy'])->name('kelas.destroy');

    // Ekstra
    Route::get('ekstra', [EkstraController::class, 'index'])->name('ekstra.index');
    Route::post('ekstra', [EkstraController::class, 'store'])->name('ekstra.store');
    Route::put('ekstra/{ekstra}', [EkstraController::class, 'update'])->name('ekstra.update');
    Route::delete('ekstra/{ekstra}', [EkstraController::class, 'destroy'])->name('ekstra.destroy');

    // Pelatih
    Route::get('pelatih', [PelatihController::class, 'index'])->name('pelatih.index');
    Route::post('pelatih', [PelatihController::class, 'store'])->name('pelatih.store');
    Route::put('pelatih/{pelatih}', [PelatihController::class, 'update'])->name('pelatih.update');
    Route::delete('pelatih/{pelatih}', [PelatihController::class, 'destroy'])->name('pelatih.destroy');

    // siswa
    Route::get('siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::post('siswa/sync', [SiswaController::class, 'sync'])->name('siswa.sync');
    Route::patch('siswa/{siswa}/ekstra', [SiswaController::class, 'setEkstra'])->name('siswa.ekstra');
    Route::delete('siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

    // laporan
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/per-kelas', [LaporanController::class, 'perKelas'])->name('laporan.per-kelas');
});

// Grup route khusus Pelatih
Route::middleware(['auth', 'role:pelatih'])->prefix('pelatih')->name('pelatih.')->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Pelatih/Dashboard');
    })->name('dashboard');
    // nanti: route kegiatan, presensi, dll.

    // kegiatan
    Route::get('kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
    Route::get('kegiatan/create', [KegiatanController::class, 'create'])->name('kegiatan.create');
    Route::post('kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
    Route::get('kegiatan/siswa', [KegiatanController::class, 'siswaByEkstra'])->name('kegiatan.siswa');
    Route::get('kegiatan/{kegiatan}/edit', [KegiatanController::class, 'edit'])->name('kegiatan.edit');
    Route::put('kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
    Route::delete('kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
});

// Profile (bawaan Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
