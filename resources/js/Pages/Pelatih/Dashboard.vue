<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name);
</script>

<template>
    <Head title="Dashboard Pelatih" />
    <AuthenticatedLayout>
        <template #header>Dashboard Pelatih</template>

        <div class="space-y-6">
            <!-- Hero Section -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0B1B36] to-[#122A52] p-8 shadow-lg transition-all hover:shadow-xl">
                <div class="relative z-10">
                    <h1 class="font-['Poppins'] text-2xl md:text-3xl font-bold text-white mb-2">
                        Selamat datang{{ userName ? `, ${userName}` : '' }} 👋
                    </h1>
                    <p class="text-[#C7D6EE] max-w-2xl text-sm md:text-base leading-relaxed">
                        Kelola kegiatan ekstrakurikuler dan penilaian peserta dengan mudah. Pilih menu di bawah ini untuk memulai aktivitas Anda hari ini.
                    </p>
                </div>
                <!-- Decorative circles -->
                <div class="absolute -right-10 -top-24 h-64 w-64 rounded-full bg-white/5 blur-2xl"></div>
                <div class="absolute right-32 -bottom-24 h-48 w-48 rounded-full bg-[#F2A93B]/10 blur-xl animate-pulse"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Manajemen Kegiatan -->
                <div class="space-y-4">
                    <h2 class="font-semibold text-gray-800 text-lg flex items-center gap-2 px-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0B1B36]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Manajemen Kegiatan
                    </h2>
                    <div class="grid grid-cols-1 gap-4">
                        <Link :href="route('pelatih.kegiatan.create')" class="group flex items-start gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-[#0B1B36]/20 hover:ring-2 hover:ring-[#0B1B36]/5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#0B1B36]/5 text-[#0B1B36] group-hover:bg-[#0B1B36] group-hover:text-[#F2A93B] group-hover:shadow-lg transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-[#0B1B36] transition-colors">Buat Kegiatan Baru</h3>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">Jadwalkan pertemuan, latihan, atau kegiatan ekstrakurikuler baru.</p>
                            </div>
                        </Link>

                        <Link :href="route('pelatih.kegiatan.index')" class="group flex items-start gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-[#0B1B36]/20 hover:ring-2 hover:ring-[#0B1B36]/5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-600 group-hover:bg-[#0B1B36] group-hover:text-[#F2A93B] group-hover:shadow-lg transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-[#0B1B36] transition-colors">History Kegiatan</h3>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">Lihat, edit, dan kelola daftar kegiatan yang telah dilaksanakan.</p>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Manajemen Penilaian -->
                <div class="space-y-4">
                    <h2 class="font-semibold text-gray-800 text-lg flex items-center gap-2 px-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#0B1B36]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        Manajemen Penilaian
                    </h2>
                    <div class="grid grid-cols-1 gap-4">
                        <Link :href="route('pelatih.penilaian.create')" class="group flex items-start gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-[#0B1B36]/20 hover:ring-2 hover:ring-[#0B1B36]/5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#0B1B36]/5 text-[#0B1B36] group-hover:bg-[#0B1B36] group-hover:text-[#F2A93B] group-hover:shadow-lg transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-[#0B1B36] transition-colors">Buat Penilaian Baru</h3>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">Berikan penilaian keterampilan atau sikap untuk peserta ekstrakurikuler.</p>
                            </div>
                        </Link>

                        <Link :href="route('pelatih.penilaian.index')" class="group flex items-start gap-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md hover:border-[#0B1B36]/20 hover:ring-2 hover:ring-[#0B1B36]/5">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-600 group-hover:bg-[#0B1B36] group-hover:text-[#F2A93B] group-hover:shadow-lg transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-[#0B1B36] transition-colors">History Penilaian</h3>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">Lihat dan edit daftar nilai yang telah Anda berikan sebelumnya.</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
