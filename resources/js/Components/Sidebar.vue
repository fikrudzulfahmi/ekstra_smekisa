<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const role = computed(() => page.props.auth?.user?.role);

// Menu untuk admin
// "match" berisi daftar nama route PERSIS yang membuat menu ini aktif.
// Tidak pakai wildcard prefix supaya menu yang berbagi awalan (mis. admin.laporan.*)
// tidak ikut nyala bersamaan.
const menuAdmin = [
    { label: 'Dashboard', route: 'admin.dashboard', icon: 'home', match: ['admin.dashboard'] },
    { label: 'Tahun Pelajaran', route: 'admin.tahun-pelajaran.index', icon: 'calendar', match: ['admin.tahun-pelajaran.*'] },
    { label: 'Kelas', route: 'admin.kelas.index', icon: 'building', match: ['admin.kelas.*'] },
    { label: 'Ekstrakurikuler', route: 'admin.ekstra.index', icon: 'flag', match: ['admin.ekstra.*'] },
    { label: 'Pelatih', route: 'admin.pelatih.index', icon: 'user', match: ['admin.pelatih.*'] },
    { label: 'Pembimbing', route: 'admin.pembimbing.index', icon: 'users', match: ['admin.pembimbing.*'] },
    { label: 'Siswa', route: 'admin.siswa.index', icon: 'users', match: ['admin.siswa.*'] },
    { label: 'Lap. Presensi (Kegiatan)', route: 'admin.laporan.index', icon: 'chart', match: ['admin.laporan.index'] },
    { label: 'Lap. Presensi (Kelas)', route: 'admin.laporan.per-kelas', icon: 'chart-pie', match: ['admin.laporan.per-kelas'] },
    { label: 'Lap. Nilai (Kegiatan)', route: 'admin.laporan.nilai', icon: 'star', match: ['admin.laporan.nilai', 'admin.laporan.nilai.show'] },
    { label: 'Lap. Nilai (Kelas)', route: 'admin.laporan.nilai.per-kelas', icon: 'star', match: ['admin.laporan.nilai.per-kelas'] },
    { label: 'Backup & Restore', route: 'admin.backup.index', icon: 'database', match: ['admin.backup.*'] },
    { label: 'Activity Log', route: 'admin.activity-log.index', icon: 'clock', match: ['admin.activity-log.*'] },
];

// Menu untuk pelatih
// "History Kegiatan" tetap aktif saat di halaman Edit (bagian dari alur history),
// tapi tidak aktif saat di halaman Buat Kegiatan (create), begitu juga sebaliknya.
const menuPelatih = [
    { label: 'Dashboard', route: 'pelatih.dashboard', icon: 'home', match: ['pelatih.dashboard'] },
    { label: 'Buat Kegiatan', route: 'pelatih.kegiatan.create', icon: 'plus', match: ['pelatih.kegiatan.create'] },
    { label: 'History Kegiatan', route: 'pelatih.kegiatan.index', icon: 'history', match: ['pelatih.kegiatan.index', 'pelatih.kegiatan.edit'] },
    { label: 'Buat Penilaian', route: 'pelatih.penilaian.create', icon: 'plus', match: ['pelatih.penilaian.create'] },
    { label: 'History Penilaian', route: 'pelatih.penilaian.index', icon: 'star', match: ['pelatih.penilaian.index', 'pelatih.penilaian.edit'] },
];

const menuPembimbing = [
    { label: 'Dashboard', route: 'pembimbing.dashboard', icon: 'home', match: ['pembimbing.dashboard'] },
    { label: 'Anggota', route: 'pembimbing.anggota.index', icon: 'users', match: ['pembimbing.anggota.*'] },
    { label: 'Pelatih', route: 'pembimbing.pelatih.index', icon: 'user', match: ['pembimbing.pelatih.*'] },
    { label: 'Lap. Presensi (Kegiatan)', route: 'pembimbing.rekap-presensi.index', icon: 'chart', match: ['pembimbing.rekap-presensi.index'] },
    { label: 'Lap. Presensi (Kelas)', route: 'pembimbing.rekap-presensi.per-kelas', icon: 'chart-pie', match: ['pembimbing.rekap-presensi.per-kelas'] },
    { label: 'Lap. Nilai (Kegiatan)', route: 'pembimbing.rekap-nilai.index', icon: 'star', match: ['pembimbing.rekap-nilai.index', 'pembimbing.rekap-nilai.show'] },
    { label: 'Lap. Nilai (Kelas)', route: 'pembimbing.rekap-nilai.per-kelas', icon: 'star', match: ['pembimbing.rekap-nilai.per-kelas'] },
];

const menu = computed(() => {
    if (role.value === 'admin') return menuAdmin;
    if (role.value === 'pembimbing') return menuPembimbing;
    return menuPelatih;
});

const roleLabel = computed(() =>
    role.value === 'admin' ? 'Admin' : role.value === 'pelatih' ? 'Pelatih' : role.value === 'pembimbing' ? 'Pembimbing' : role.value,
);

const isActive = (item) => item.match.some((pattern) => route().current(pattern));
</script>

<template>
    <!-- Overlay (mobile only) -->
    <div
        v-if="open"
        class="fixed inset-0 z-30 bg-[#0B1B36]/60 backdrop-blur-sm lg:hidden"
        @click="emit('close')"
    ></div>

    <aside
        :class="[
            'fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-[#0B1B36] font-\'Inter\' text-white transition-transform duration-200 ease-in-out print:hidden lg:static lg:translate-x-0',
            open ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <!-- Brand -->
        <div class="flex items-center justify-center border-b border-white/10 px-5 py-6">
            <img
                src="/images/landing/logo-sekolah.png"
                alt="Logo SMK Islam 1 Kota Blitar"
                class="h-16 w-auto shrink-0 object-contain"
            />
        </div>

        <div class="px-5 pt-4">
            <span class="inline-flex items-center rounded-full bg-[#F2A93B]/10 px-3 py-1 text-xs font-semibold capitalize text-[#F2A93B]">
                {{ roleLabel }}
            </span>
        </div>

        <!-- Menu -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" aria-label="Menu utama">
            <Link
                v-for="m in menu"
                :key="m.route"
                :href="route(m.route)"
                @click="emit('close')"
                :class="[
                    'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                    isActive(m)
                        ? 'bg-[#F2A93B]/15 text-[#F2A93B]'
                        : 'text-[#C7D6EE] hover:bg-white/5 hover:text-white',
                ]"
            >
                <svg
                    class="h-5 w-5 shrink-0"
                    :class="isActive(m) ? 'text-[#F2A93B]' : 'text-[#6C82AC] group-hover:text-white'"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path v-if="m.icon === 'home'" stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4a1 1 0 001-1v-5h2v5a1 1 0 001 1h4a1 1 0 001-1V10" />
                    <path v-else-if="m.icon === 'calendar'" stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                    <path v-else-if="m.icon === 'building'" stroke-linecap="round" stroke-linejoin="round" d="M4 21V5a1 1 0 011-1h6a1 1 0 011 1v16M4 21h16M12 21v-6a1 1 0 011-1h4a1 1 0 011 1v6M8 7h.01M8 11h.01M8 15h.01" />
                    <path v-else-if="m.icon === 'flag'" stroke-linecap="round" stroke-linejoin="round" d="M5 3v18M5 4h11l-2 4 2 4H5" />
                    <path v-else-if="m.icon === 'user'" stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0" />
                    <path v-else-if="m.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-7.13a4 4 0 110 8 4 4 0 010-8zm7 4a4 4 0 00-2-3.46M6 9a4 4 0 012-3.46" />
                    <path v-else-if="m.icon === 'chart'" stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M8 17V10m5 7V6m5 11v-4" />
                    <path v-else-if="m.icon === 'chart-pie'" stroke-linecap="round" stroke-linejoin="round" d="M11 3.05A9 9 0 1020.95 13H11V3.05z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    <path v-else-if="m.icon === 'plus'" stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    <path v-else-if="m.icon === 'history'" stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8M3 3v5h5M12 7v5l3 3" />
                    <path v-else-if="m.icon === 'star'" stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385c.117.489-.417.877-.849.6l-4.725-2.885a.563.563 0 00-.588 0l-4.725 2.885c-.432.277-.966-.11-.849-.6l1.285-5.385a.562.562 0 00-.182-.557l-4.204-3.602c-.38-.325-.178-.947.321-.988l5.518-.442a.563.563 0 00.475-.345l2.125-5.11z" />
                    <path v-else-if="m.icon === 'database'" stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                    <path v-else-if="m.icon === 'clock'" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ m.label }}
            </Link>
        </nav>
    </aside>
</template>