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
use App\Http\Controllers\Admin\LaporanHrController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Pelatih\KegiatanController;
use App\Http\Controllers\Pelatih\PenilaianController;
use App\Http\Controllers\Superadmin\AkunController as SuperadminAkunController;
use App\Http\Controllers\Superadmin\BackupController as SuperadminBackupController;
use App\Http\Controllers\Superadmin\ActivityLogController as SuperadminActivityLogController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'tahunAktif' => \App\Models\TahunPelajaran::where('is_aktif', true)->value('nama'),
        'stats' => [
            'ekstra' => \App\Models\Ekstra::count(),
            'siswa' => \App\Models\Siswa::count(),
            'pelatih' => \App\Models\User::where('role', 'pelatih')->count(),
            'kegiatan' => \App\Models\Kegiatan::count(),
        ]
    ]);
});

// Redirect setelah login → arahkan sesuai role
Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    if ($role === 'superadmin') {
        return redirect()->route('superadmin.akun.index');
    } elseif ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($role === 'pembimbing') {
        return redirect()->route('pembimbing.dashboard');
    }
    return redirect()->route('pelatih.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grup route khusus Superadmin
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    // Akun management
    Route::get('akun', [SuperadminAkunController::class, 'index'])->name('akun.index');
    Route::post('akun', [SuperadminAkunController::class, 'store'])->name('akun.store');
    Route::put('akun/{user}', [SuperadminAkunController::class, 'update'])->name('akun.update');
    Route::patch('akun/{user}/reset-password', [SuperadminAkunController::class, 'resetPassword'])->name('akun.reset-password');
    Route::delete('akun/{user}', [SuperadminAkunController::class, 'destroy'])->name('akun.destroy');

    // Backup & Restore
    Route::get('backup', [SuperadminBackupController::class, 'index'])->name('backup.index');
    Route::post('backup', [SuperadminBackupController::class, 'create'])->name('backup.create');
    Route::delete('backup', [SuperadminBackupController::class, 'destroy'])->name('backup.destroy');
    Route::get('backup/download', [SuperadminBackupController::class, 'download'])->name('backup.download');
    Route::post('backup/restore', [SuperadminBackupController::class, 'restore'])->name('backup.restore');

    // Activity Log
    Route::get('activity-log', [SuperadminActivityLogController::class, 'index'])->name('activity-log.index');
    Route::post('activity-log/clear', [SuperadminActivityLogController::class, 'clear'])->name('activity-log.clear');
});

// Grup route khusus Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Tahun Pelajaran
    Route::get('tahun-pelajaran', [TahunPelajaranController::class, 'index'])->name('tahun-pelajaran.index');
    Route::post('tahun-pelajaran', [TahunPelajaranController::class, 'store'])->name('tahun-pelajaran.store');
    Route::put('tahun-pelajaran/{tahunPelajaran}', [TahunPelajaranController::class, 'update'])->name('tahun-pelajaran.update');
    Route::patch('tahun-pelajaran/{tahunPelajaran}/aktif', [TahunPelajaranController::class, 'setAktif'])->name('tahun-pelajaran.aktif');
    Route::delete('tahun-pelajaran/{tahunPelajaran}', [TahunPelajaranController::class, 'destroy'])->name('tahun-pelajaran.destroy');

    // Kelas
    Route::get('kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::post('kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::put('kelas/{kela}', [KelasController::class, 'update'])->name('kelas.update');
    Route::delete('kelas/{kela}', [KelasController::class, 'destroy'])->name('kelas.destroy');

    // Backup
    Route::get('backup', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup.index');
    Route::post('backup', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backup.create');
    Route::delete('backup', [App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backup.destroy');
    Route::get('backup/download', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backup.download');
    Route::post('backup/restore', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backup.restore');

    // Activity Log
    Route::get('activity-log', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::post('activity-log/clear', [App\Http\Controllers\Admin\ActivityLogController::class, 'clear'])->name('activity-log.clear');

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

    // Pembimbing
    Route::get('pembimbing', [App\Http\Controllers\Admin\PembimbingController::class, 'index'])->name('pembimbing.index');
    Route::post('pembimbing', [App\Http\Controllers\Admin\PembimbingController::class, 'store'])->name('pembimbing.store');
    Route::put('pembimbing/{pembimbing}', [App\Http\Controllers\Admin\PembimbingController::class, 'update'])->name('pembimbing.update');
    Route::delete('pembimbing/{pembimbing}', [App\Http\Controllers\Admin\PembimbingController::class, 'destroy'])->name('pembimbing.destroy');

    // siswa
    Route::get('siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::post('siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::post('siswa/sync', [SiswaController::class, 'sync'])->name('siswa.sync');
    Route::patch('siswa/{siswa}/ekstra', [SiswaController::class, 'setEkstra'])->name('siswa.ekstra');
    Route::delete('siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

    // laporan presensi
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/per-kelas', [LaporanController::class, 'perKelas'])->name('laporan.per-kelas');
    Route::get('laporan/per-kelas/export', [LaporanController::class, 'exportPerKelas'])->name('laporan.per-kelas.export');

    // laporan nilai
    Route::get('laporan/nilai', [LaporanController::class, 'nilaiIndex'])->name('laporan.nilai');
    Route::get('laporan/nilai/per-kelas', [LaporanController::class, 'nilaiPerKelas'])->name('laporan.nilai.per-kelas');
    Route::get('laporan/nilai/per-kelas/export', [LaporanController::class, 'exportNilaiPerKelas'])->name('laporan.nilai.per-kelas.export');
    Route::get('laporan/nilai/{penilaian}', [LaporanController::class, 'nilaiShow'])->name('laporan.nilai.show');

    // laporan hr pelatih
    Route::get('laporan-hr', [LaporanHrController::class, 'index'])->name('laporan-hr.index');
    Route::get('laporan-hr/cetak', [LaporanHrController::class, 'print'])->name('laporan-hr.cetak');

    // settings
    Route::get('setting', [SettingController::class, 'index'])->name('setting.index');
    Route::post('setting', [SettingController::class, 'update'])->name('setting.update');
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

    // penilaian
    Route::get('penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
    Route::get('penilaian/create', [PenilaianController::class, 'create'])->name('penilaian.create');
    Route::get('penilaian/siswa', [PenilaianController::class, 'siswa'])->name('penilaian.siswa');
    Route::post('penilaian', [PenilaianController::class, 'store'])->name('penilaian.store');
    Route::get('penilaian/{penilaian}/edit', [PenilaianController::class, 'edit'])->name('penilaian.edit');
    Route::put('penilaian/{penilaian}', [PenilaianController::class, 'update'])->name('penilaian.update');
    Route::delete('penilaian/{penilaian}', [PenilaianController::class, 'destroy'])->name('penilaian.destroy');
});

// Grup route khusus Pembimbing
Route::middleware(['auth', 'role:pembimbing'])->prefix('pembimbing')->name('pembimbing.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Pembimbing\DashboardController::class, 'index'])->name('dashboard');

    // Anggota
    Route::get('anggota', [App\Http\Controllers\Pembimbing\AnggotaController::class, 'index'])->name('anggota.index');
    Route::patch('anggota/{siswa}/ekstra', [App\Http\Controllers\Pembimbing\AnggotaController::class, 'setEkstra'])->name('anggota.ekstra');

    // Rekap Presensi & Nilai
    Route::get('rekap-presensi', [App\Http\Controllers\Pembimbing\RekapPresensiController::class, 'index'])->name('rekap-presensi.index');
    Route::get('rekap-presensi/per-kelas', [App\Http\Controllers\Pembimbing\RekapPresensiController::class, 'perKelas'])->name('rekap-presensi.per-kelas');
    
    Route::get('rekap-nilai', [App\Http\Controllers\Pembimbing\RekapNilaiController::class, 'index'])->name('rekap-nilai.index');
    Route::get('rekap-nilai/per-kelas', [App\Http\Controllers\Pembimbing\RekapNilaiController::class, 'perKelas'])->name('rekap-nilai.per-kelas');
    Route::get('rekap-nilai/per-kelas/export', [App\Http\Controllers\Pembimbing\RekapNilaiController::class, 'exportPerKelas'])->name('rekap-nilai.per-kelas.export');
    Route::get('rekap-nilai/{penilaian}', [App\Http\Controllers\Pembimbing\RekapNilaiController::class, 'show'])->name('rekap-nilai.show');

    // Pelatih
    Route::get('pelatih', [App\Http\Controllers\Pembimbing\PelatihController::class, 'index'])->name('pelatih.index');
    Route::post('pelatih', [App\Http\Controllers\Pembimbing\PelatihController::class, 'store'])->name('pelatih.store');
    Route::put('pelatih/{pelatih}', [App\Http\Controllers\Pembimbing\PelatihController::class, 'update'])->name('pelatih.update');
    Route::delete('pelatih/{pelatih}', [App\Http\Controllers\Pembimbing\PelatihController::class, 'destroy'])->name('pelatih.destroy');
});

// Profile (bawaan Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
