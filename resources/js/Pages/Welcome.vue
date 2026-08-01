<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    tahunAktif: {
        type: String,
        default: null,
    },
    stats: {
        type: Object,
        default: () => ({
            ekstra: 0,
            siswa: 0,
            pelatih: 0,
            kegiatan: 0,
        })
    }
});

const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="relative min-h-screen bg-[#FAFAFA] font-['Inter'] text-[#101828] antialiased overflow-hidden selection:bg-[#F2A93B] selection:text-[#0B1B36]">
        <Head title="Beranda" />

        <!-- Latar Belakang Pola Grid -->
        <div class="absolute inset-0 z-0 bg-grid opacity-30"></div>

        <!-- Ambient Glows -->
        <div class="pointer-events-none absolute -left-40 -top-40 z-0 h-[500px] w-[500px] rounded-full bg-[#3E6FD9] opacity-10 blur-[120px]"></div>
        <div class="pointer-events-none absolute -bottom-40 -right-40 z-0 h-[600px] w-[600px] rounded-full bg-[#F2A93B] opacity-15 blur-[150px]"></div>

        <div class="relative z-10 flex min-h-screen flex-col">
            <!-- NAVBAR MELAYANG (FLOATING PILL) -->
            <header class="pt-6 px-4 sm:px-6 lg:px-8">
                <nav class="mx-auto flex max-w-6xl items-center justify-between rounded-full bg-white/80 px-4 py-3 shadow-[0_8px_30px_rgb(0,0,0,0.06)] backdrop-blur-xl border border-white/40 sm:px-6">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <img src="/images/landing/logo-sekolah.png" alt="Logo SMEKISA" class="h-9 w-9 object-contain" />
                        <div class="hidden sm:block leading-tight">
                            <p class="font-['Poppins'] text-sm font-bold tracking-wide text-[#0B1B36]">SMEKISA</p>
                            <p class="text-[10px] font-medium text-[#5B6472]">Skill by Discipline and Religious</p>
                        </div>
                    </div>

                    <!-- Menu Desktop -->
                    <div class="hidden items-center gap-8 md:flex">
                        <a href="#" class="text-sm font-semibold text-[#5B6472] transition hover:text-[#0B1B36]">Beranda</a>
                        <a href="#" class="text-sm font-semibold text-[#5B6472] transition hover:text-[#0B1B36]">Fitur</a>
                        <a href="https://smkislam1blitar.sch.id" target="_blank" class="text-sm font-semibold text-[#5B6472] transition hover:text-[#0B1B36]">Website Utama</a>
                    </div>

                    <!-- Call to Action -->
                    <div class="flex items-center gap-3">
                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="rounded-full bg-gradient-to-r from-[#F2A93B] to-[#f5b85e] px-6 py-2.5 text-sm font-bold text-[#0B1B36] shadow-lg shadow-[#F2A93B]/30 transition hover:scale-105 hover:shadow-xl"
                            >
                                Dashboard
                            </Link>
                            <Link
                                v-else
                                :href="route('login')"
                                class="rounded-full bg-gradient-to-r from-[#F2A93B] to-[#f5b85e] px-6 py-2.5 text-sm font-bold text-[#0B1B36] shadow-lg shadow-[#F2A93B]/30 transition hover:scale-105 hover:shadow-xl"
                            >
                                Masuk
                            </Link>
                        </template>

                        <!-- Mobile Menu Button -->
                        <button class="md:hidden rounded-full p-2 text-[#0B1B36] bg-gray-50" @click="mobileMenuOpen = !mobileMenuOpen">
                            <svg v-if="!mobileMenuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </nav>

                <!-- Mobile Menu Dropdown -->
                <div v-if="mobileMenuOpen" class="mx-auto mt-2 max-w-6xl overflow-hidden rounded-3xl bg-white shadow-xl md:hidden">
                    <div class="flex flex-col px-6 py-4 space-y-4">
                        <a href="#" class="text-sm font-semibold text-[#5B6472]" @click="mobileMenuOpen = false">Beranda</a>
                        <a href="#" class="text-sm font-semibold text-[#5B6472]" @click="mobileMenuOpen = false">Fitur</a>
                        <a href="https://smkislam1blitar.sch.id" target="_blank" class="text-sm font-semibold text-[#5B6472]">Website Utama</a>
                    </div>
                </div>
            </header>

            <!-- HERO SECTION -->
            <main class="flex-1 flex items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-7xl items-center gap-16 lg:grid-cols-2">
                    
                    <!-- Kiri: Teks -->
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 shadow-sm mb-6">
                            <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                            <span class="text-xs font-semibold text-[#5B6472]">Sistem Terpadu Aktif {{ tahunAktif ? tahunAktif : '-' }}</span>
                        </div>

                        <h1 class="font-['Poppins'] text-5xl font-black leading-[1.1] text-[#0B1B36] tracking-tight sm:text-6xl md:text-7xl">
                            Presensi Digital <br />
                            <span class="text-[#F2A93B]">Ekstrakurikuler</span> <br />
                            SMEKISA
                        </h1>
                        
                        <p class="mx-auto mt-6 max-w-lg text-base leading-relaxed text-[#5B6472] sm:text-lg lg:mx-0">
                            Wadah pencatatan kehadiran dan penilaian ekstrakurikuler 
                            secara real-time, cepat, dan transparan. Akses mudah untuk Admin, Pelatih, dan Siswa.
                        </p>

                        <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row lg:justify-start">
                            <Link
                                v-if="canLogin"
                                :href="$page.props.auth.user ? route('dashboard') : route('login')"
                                class="w-full sm:w-auto rounded-full bg-gradient-to-r from-[#0B1B36] to-[#1a2f57] px-8 py-4 text-center text-sm font-bold text-white shadow-xl shadow-[#0B1B36]/20 transition-all hover:-translate-y-1 hover:shadow-2xl"
                            >
                                {{ $page.props.auth.user ? 'Buka Dashboard Sekarang' : 'Masuk ke Aplikasi' }}
                            </Link>
                        </div>
                    </div>

                    <!-- Kanan: Bento Box / Statistik -->
                    <div class="relative w-full max-w-lg mx-auto lg:max-w-none">
                        <div class="absolute -inset-1 rounded-[2.5rem] bg-gradient-to-tr from-[#3E6FD9]/20 to-[#F2A93B]/20 blur-2xl"></div>
                        
                        <div class="relative rounded-[2rem] bg-white p-6 shadow-[0_20px_50px_rgba(0,0,0,0.06)] border border-white/60 sm:p-8 backdrop-blur-sm">
                            <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                                <p class="font-['Poppins'] text-sm font-semibold text-[#5B6472]">Statistik Ekstrakurikuler</p>
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 sm:gap-5">
                                <!-- Box 1 -->
                                <div class="flex flex-col justify-center rounded-2xl bg-emerald-50 p-5 transition-transform hover:-translate-y-1">
                                    <span class="font-['Poppins'] text-3xl font-black text-emerald-900 sm:text-4xl">{{ stats.ekstra }}</span>
                                    <span class="mt-1 text-xs font-medium text-emerald-600 sm:text-sm">Pilihan Ekstra</span>
                                </div>
                                
                                <!-- Box 2 -->
                                <div class="flex flex-col justify-center rounded-2xl bg-amber-50 p-5 transition-transform hover:-translate-y-1">
                                    <span class="font-['Poppins'] text-3xl font-black text-amber-900 sm:text-4xl">{{ stats.siswa }}</span>
                                    <span class="mt-1 text-xs font-medium text-amber-600 sm:text-sm">Siswa Aktif</span>
                                </div>
                                
                                <!-- Box 3 -->
                                <div class="flex flex-col justify-center rounded-2xl bg-blue-50 p-5 transition-transform hover:-translate-y-1">
                                    <span class="font-['Poppins'] text-3xl font-black text-blue-900 sm:text-4xl">{{ stats.pembina }}</span>
                                    <span class="mt-1 text-xs font-medium text-blue-600 sm:text-sm">Jumlah Pembina</span>
                                </div>
                                
                                <!-- Box 4 -->
                                <div class="flex flex-col justify-center rounded-2xl bg-purple-50 p-5 transition-transform hover:-translate-y-1">
                                    <span class="font-['Poppins'] text-3xl font-black text-purple-900 sm:text-4xl">{{ stats.pelatih }}</span>
                                    <span class="mt-1 text-xs font-medium text-purple-600 sm:text-sm">Pelatih Ahli</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </main>
            
            <footer class="py-6 text-center text-sm font-medium text-gray-400">
                &copy; {{ new Date().getFullYear() }} SMKS Islam 1 Kota Blitar. All rights reserved.
            </footer>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800;900&display=swap');

.bg-grid {
    background-size: 40px 40px;
    background-image: 
        linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
    mask-image: linear-gradient(to bottom, white 40%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, white 40%, transparent 100%);
}
</style>
