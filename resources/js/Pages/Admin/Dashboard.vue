<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    totalEkstra: {
        type: Number,
        default: 0,
    },
    totalPelatih: {
        type: Number,
        default: 0,
    },
    siswaIkutEkstra: {
        type: Number,
        default: 0,
    },
    siswaBelumEkstra: {
        type: Number,
        default: 0,
    },
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name);

const totalSiswa = computed(() => props.siswaIkutEkstra + props.siswaBelumEkstra);

const persentaseIkut = computed(() => {
    if (totalSiswa.value === 0) return 0;
    return Math.round((props.siswaIkutEkstra / totalSiswa.value) * 100);
});

const stats = computed(() => [
    {
        label: 'Ekstrakurikuler Aktif',
        value: props.totalEkstra,
        icon: 'flag',
        accent: 'bg-[#0B1B36]',
        iconColor: 'text-[#F2A93B]',
    },
    {
        label: 'Pelatih Terdaftar',
        value: props.totalPelatih,
        icon: 'user',
        accent: 'bg-[#3E6FD9]',
        iconColor: 'text-white',
    },
    {
        label: 'Siswa Ikut Ekstra',
        value: props.siswaIkutEkstra,
        icon: 'check',
        accent: 'bg-emerald-600',
        iconColor: 'text-white',
    },
    {
        label: 'Siswa Belum Ikut Ekstra',
        value: props.siswaBelumEkstra,
        icon: 'alert',
        accent: 'bg-amber-500',
        iconColor: 'text-white',
    },
]);
</script>

<template>
    <Head title="Dashboard Admin" />
    <AuthenticatedLayout>
        <template #header>Dashboard Admin</template>

        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Sambutan -->
            <div class="mb-8">
                <h1 class="font-['Poppins'] text-xl font-semibold text-[#0B1B36]">
                    Selamat datang{{ userName ? `, ${userName}` : '' }} 👋
                </h1>
                <p class="mt-1 text-sm text-[#5B6472]">
                    Ringkasan data ekstrakurikuler SMK Islam 1 Kota Blitar hari ini.
                </p>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="s in stats"
                    :key="s.label"
                    class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div :class="['flex h-11 w-11 items-center justify-center rounded-xl', s.accent]">
                        <svg v-if="s.icon === 'flag'" :class="['h-5 w-5', s.iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v18M5 4h11l-2 4 2 4H5" />
                        </svg>
                        <svg v-else-if="s.icon === 'user'" :class="['h-5 w-5', s.iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0" />
                        </svg>
                        <svg v-else-if="s.icon === 'check'" :class="['h-5 w-5', s.iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-7.13a4 4 0 110 8 4 4 0 010-8zm-8 5l2 2 4-4" />
                        </svg>
                        <svg v-else :class="['h-5 w-5', s.iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a1 1 0 00.87 1.5h18.62a1 1 0 00.87-1.5L13.71 3.86a1 1 0 00-1.72 0z" />
                        </svg>
                    </div>

                    <p class="mt-4 font-['Poppins'] text-3xl font-bold text-[#0B1B36]">
                        {{ s.value.toLocaleString('id-ID') }}
                    </p>
                    <p class="mt-1 text-sm text-[#5B6472]">{{ s.label }}</p>
                </div>
            </div>

            <!-- Progress partisipasi -->
            <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-medium text-[#1B2333]">
                        Partisipasi Siswa dalam Ekstrakurikuler
                    </p>
                    <p class="font-['Poppins'] text-sm font-semibold text-[#0B1B36]">
                        {{ persentaseIkut }}% dari {{ totalSiswa.toLocaleString('id-ID') }} siswa
                    </p>
                </div>
                <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-[#F4F7FC]">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-[#3E6FD9] to-[#F2A93B] transition-all duration-500"
                        :style="{ width: persentaseIkut + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Log Aktivitas -->
            <div class="mt-10">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                        Log Aktivitas
                    </h2>
                </div>

                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#D8E1F0] bg-white/60 px-6 py-14 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F4F7FC]">
                        <svg class="h-6 w-6 text-[#6C82AC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 7h6m-6 4h6" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-medium text-[#1B2333]">
                        Tabel log aktivitas belum tersedia
                    </p>
                    <p class="mt-1 max-w-sm text-sm text-[#5B6472]">
                        Bagian ini akan menampilkan riwayat aktivitas terbaru begitu
                        fiturnya selesai dibuat.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
