<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    jumlahEkstra: Number,
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name);
</script>

<template>
    <Head title="Dashboard Pembina" />
    <AuthenticatedLayout>
        <template #header>Dashboard Pembina</template>

        <div class="space-y-6">
            <!-- Hero Section -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 p-8 shadow-lg transition-all hover:shadow-xl">
                <div class="relative z-10">
                    <h1 class="font-['Poppins'] text-2xl md:text-3xl font-bold text-white mb-2">
                        Selamat datang{{ userName ? `, ${userName}` : '' }} 👋
                    </h1>
                    <p class="text-blue-50 max-w-2xl text-sm md:text-base leading-relaxed">
                        Anda saat ini mengelola <span class="font-bold text-white">{{ jumlahEkstra }} ekstrakurikuler</span>. Pantau aktivitas anggota, pelatih, presensi, dan rekapitulasi nilai dengan mudah melalui panel di bawah ini.
                    </p>
                </div>
                <!-- Decorative circles -->
                <div class="absolute -right-10 -top-24 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute right-32 -bottom-24 h-48 w-48 rounded-full bg-indigo-400/20 blur-xl animate-pulse"></div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Kelola Anggota -->
                <Link :href="route('pembimbing.anggota.index')" class="group flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-blue-200 hover:ring-2 hover:ring-blue-50">
                    <div>
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 group-hover:bg-gradient-to-br group-hover:from-blue-500 group-hover:to-blue-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-blue-200 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">Anggota Ekstra</h3>
                        <p class="mt-2 text-sm text-gray-500 line-clamp-2">Lihat dan kelola daftar siswa yang tergabung di ekstrakurikuler Anda.</p>
                    </div>
                    <div class="mt-4 flex items-center text-sm font-medium text-blue-600 group-hover:text-blue-700">
                        Kelola Sekarang <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </Link>

                <!-- Kelola Pelatih -->
                <Link :href="route('pembimbing.pelatih.index')" class="group flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-indigo-200 hover:ring-2 hover:ring-indigo-50">
                    <div>
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-gradient-to-br group-hover:from-indigo-500 group-hover:to-indigo-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-indigo-200 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">Data Pelatih</h3>
                        <p class="mt-2 text-sm text-gray-500 line-clamp-2">Pantau daftar pelatih yang ditugaskan membimbing kegiatan.</p>
                    </div>
                    <div class="mt-4 flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                        Lihat Pelatih <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </Link>

                <!-- Laporan Presensi -->
                <Link :href="route('pembimbing.rekap-presensi.index')" class="group flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-violet-200 hover:ring-2 hover:ring-violet-50">
                    <div>
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600 group-hover:bg-gradient-to-br group-hover:from-violet-500 group-hover:to-violet-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-violet-200 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-violet-700 transition-colors">Rekap Presensi</h3>
                        <p class="mt-2 text-sm text-gray-500 line-clamp-2">Pantau tingkat kehadiran siswa dalam setiap kegiatan ekstra.</p>
                    </div>
                    <div class="mt-4 flex items-center text-sm font-medium text-violet-600 group-hover:text-violet-700">
                        Cek Laporan <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </Link>

                <!-- Laporan Nilai -->
                <Link :href="route('pembimbing.rekap-nilai.index')" class="group flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-amber-200 hover:ring-2 hover:ring-amber-50">
                    <div>
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 group-hover:bg-gradient-to-br group-hover:from-amber-400 group-hover:to-amber-500 group-hover:text-white group-hover:shadow-lg group-hover:shadow-amber-200 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-amber-700 transition-colors">Rekap Nilai</h3>
                        <p class="mt-2 text-sm text-gray-500 line-clamp-2">Lihat dan evaluasi nilai akhir setiap anggota ekstrakurikuler.</p>
                    </div>
                    <div class="mt-4 flex items-center text-sm font-medium text-amber-600 group-hover:text-amber-700">
                        Cek Nilai <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </Link>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
