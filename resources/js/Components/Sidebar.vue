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
const menuAdmin = [
    { label: 'Dashboard', route: 'admin.dashboard', icon: 'home' },
    { label: 'Tahun Pelajaran', route: 'admin.tahun-pelajaran.index', icon: 'calendar' },
    { label: 'Kelas', route: 'admin.kelas.index', icon: 'building' },
    { label: 'Ekstrakurikuler', route: 'admin.ekstra.index', icon: 'flag' },
    { label: 'Pelatih', route: 'admin.pelatih.index', icon: 'user' },
    { label: 'Siswa', route: 'admin.siswa.index', icon: 'users' },
    { label: 'Laporan per Kegiatan', route: 'admin.laporan.index', icon: 'chart' },
    { label: 'Laporan per Kelas', route: 'admin.laporan.per-kelas', icon: 'chart-pie' },
];

// Menu untuk pelatih
const menuPelatih = [
    { label: 'Dashboard', route: 'pelatih.dashboard', icon: 'home' },
    { label: 'Buat Kegiatan', route: 'pelatih.kegiatan.create', icon: 'plus' },
    { label: 'History Kegiatan', route: 'pelatih.kegiatan.index', icon: 'history' },
];

const menu = computed(() => (role.value === 'admin' ? menuAdmin : menuPelatih));

const roleLabel = computed(() =>
    role.value === 'admin' ? 'Admin' : role.value === 'pelatih' ? 'Pelatih' : role.value,
);

const isActive = (routeName) => {
    // aktif jika route saat ini diawali dengan nama menu (tanpa .index dsb)
    const base = routeName.replace(/\.(index|create)$/, '');
    return route().current(base) || route().current(routeName) || route().current(base + '.*');
};
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
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            <img
                src="/images/landing/logo-sekolah.png"
                alt="Logo SMK Islam 1 Kota Blitar"
                class="h-10 w-10 shrink-0 object-contain"
            />
            <div class="min-w-0 leading-tight">
                <p class="truncate font-['Poppins'] text-sm font-bold text-white">
                    Presensi Ekskul
                </p>
                <p class="text-xs text-[#9CC3F0]">SMEKISA</p>
            </div>
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
                    isActive(m.route)
                        ? 'bg-[#F2A93B]/15 text-[#F2A93B]'
                        : 'text-[#C7D6EE] hover:bg-white/5 hover:text-white',
                ]"
            >
                <svg
                    class="h-5 w-5 shrink-0"
                    :class="isActive(m.route) ? 'text-[#F2A93B]' : 'text-[#6C82AC] group-hover:text-white'"
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
                </svg>
                {{ m.label }}
            </Link>
        </nav>
    </aside>
</template>
