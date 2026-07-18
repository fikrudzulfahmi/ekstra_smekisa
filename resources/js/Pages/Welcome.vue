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
});

const mobileMenuOpen = ref(false);

const features = [
    {
        title: 'Presensi Real-time',
        desc: 'Kehadiran siswa tercatat langsung saat presensi dibuka pelatih, tanpa rekap manual di kertas.',
        icon: 'clock',
    },
    {
        title: 'Riwayat Kehadiran',
        desc: 'Siswa dan pelatih dapat melihat riwayat kehadiran ekstrakurikuler kapan saja lewat aplikasi.',
        icon: 'history',
    },
    {
        title: 'Laporan Per Kelas',
        desc: 'Admin dan wali kelas bisa memantau rekap kehadiran per kelas maupun per ekstrakurikuler.',
        icon: 'chart',
    },
    {
        title: 'Multi Peran Pengguna',
        desc: 'Akses terpisah untuk admin, pelatih, dan siswa, masing-masing sesuai kebutuhannya.',
        icon: 'users',
    },
];

const highlights = [
    'Presensi Real-time',
    'Laporan Per Kelas',
    'Multi Ekstrakurikuler',
    'Akses Pelatih & Admin',
];
</script>

<template>
    <Head title="Beranda" />

    <div class="min-h-screen bg-white font-['Inter'] text-[#101828] antialiased">
        <!-- ===== NAVBAR ===== -->
        <header class="fixed inset-x-0 top-0 z-50 bg-[#0B1B36]/80 backdrop-blur-md">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3 lg:px-8">
                <div class="flex items-center gap-3">
                    <img
                        src="/images/landing/logo-sekolah.png"
                        alt="Logo SMK Islam 1 Kota Blitar"
                        class="h-11 w-11 object-contain"
                    />
                    <div class="leading-tight">
                        <p class="font-['Poppins'] text-sm font-bold tracking-wide text-white">
                            SMKS ISLAM 1 KOTA BLITAR
                        </p>
                        <p class="text-xs text-[#9CC3F0]">Presensi Ekstrakurikuler</p>
                    </div>
                </div>

                <div class="hidden items-center gap-8 lg:flex">
                    <a href="#beranda" class="text-sm font-medium text-white/80 transition hover:text-white">Beranda</a>
                    <a href="#tentang" class="text-sm font-medium text-white/80 transition hover:text-white">Tentang Sekolah</a>
                    <a href="#fitur" class="text-sm font-medium text-white/80 transition hover:text-white">Fitur Aplikasi</a>
                    <a href="https://smkislam1blitar.sch.id" target="_blank" class="text-sm font-medium text-white/80 transition hover:text-white">Website Sekolah</a>
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    <template v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-full bg-[#F2A93B] px-5 py-2 text-sm font-semibold text-[#0B1B36] transition hover:bg-[#f7c878]"
                        >
                            Dashboard
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="rounded-full bg-[#F2A93B] px-5 py-2 text-sm font-semibold text-[#0B1B36] transition hover:bg-[#f7c878]"
                        >
                            Masuk Aplikasi
                        </Link>
                    </template>
                </div>

                <button
                    class="text-white lg:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    aria-label="Buka menu"
                >
                    <svg v-if="!mobileMenuOpen" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                    <svg v-else class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="space-y-1 bg-[#0B1B36] px-6 pb-5 lg:hidden">
                <a href="#beranda" class="block py-2 text-sm font-medium text-white/80" @click="mobileMenuOpen = false">Beranda</a>
                <a href="#tentang" class="block py-2 text-sm font-medium text-white/80" @click="mobileMenuOpen = false">Tentang Sekolah</a>
                <a href="#fitur" class="block py-2 text-sm font-medium text-white/80" @click="mobileMenuOpen = false">Fitur Aplikasi</a>
                <a href="https://smkislamblitar.sch.id" target="_blank" class="block py-2 text-sm font-medium text-white/80">Website Sekolah</a>
                <template v-if="canLogin">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="mt-2 block w-fit rounded-full bg-[#F2A93B] px-5 py-2 text-sm font-semibold text-[#0B1B36]"
                    >
                        Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="mt-2 block w-fit rounded-full bg-[#F2A93B] px-5 py-2 text-sm font-semibold text-[#0B1B36]"
                    >
                        Masuk Aplikasi
                    </Link>
                </template>
            </div>
        </header>

        <!-- ===== HERO ===== -->
        <section id="beranda" class="relative overflow-hidden bg-[#0B1B36] pt-28">
            <!-- decorative glow -->
            <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#F2A93B] opacity-20 blur-[110px]"></div>
            <div class="pointer-events-none absolute -left-32 top-40 h-96 w-96 rounded-full bg-[#3E6FD9] opacity-30 blur-[110px]"></div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 pb-20 pt-10 lg:grid-cols-2 lg:gap-8 lg:px-8 lg:pb-28">
                <div class="relative z-10">
                    <span class="inline-flex items-center rounded-full border border-[#F2A93B]/40 bg-[#F2A93B]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-[#F2A93B]">
                        Sistem Presensi Ekstrakurikuler
                    </span>

                    <h1 class="mt-6 font-['Poppins'] text-4xl font-bold leading-[1.15] text-white sm:text-5xl">
                        Presensi Ekstrakurikuler,
                        <span class="text-[#F2A93B]">Lebih Cepat &amp; Rapi</span>
                    </h1>

                    <p class="mt-6 max-w-lg text-base leading-relaxed text-[#C7D6EE]">
                        Aplikasi presensi digital untuk siswa, pelatih, dan admin ekstrakurikuler
                        SMKS Islam 1 Kota Blitar. Catat kehadiran secara real-time, pantau
                        rekapnya kapan saja, tanpa perlu presensi kertas lagi.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <Link
                            v-if="canLogin"
                            :href="$page.props.auth.user ? route('dashboard') : route('login')"
                            class="rounded-full bg-[#F2A93B] px-7 py-3 text-sm font-semibold text-[#0B1B36] shadow-lg shadow-[#F2A93B]/20 transition hover:bg-[#f7c878]"
                        >
                            {{ $page.props.auth.user ? 'Buka Dashboard' : 'Masuk ke Aplikasi' }}
                        </Link>
                        <a
                            href="#tentang"
                            class="rounded-full border border-white/30 px-7 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Tentang Sekolah
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-x-6 gap-y-3">
                        <div
                            v-for="item in highlights"
                            :key="item"
                            class="flex items-center gap-2 text-sm text-[#C7D6EE]"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-[#F2A93B]"></span>
                            {{ item }}
                        </div>
                    </div>
                </div>

                <div class="relative z-10">
                    <div class="overflow-hidden rounded-3xl border border-white/10 shadow-2xl">
                        <img
                            src="/images/landing/hero-mpls.jpg"
                            alt="Siswa SMKS Islam 1 Kota Blitar"
                            class="h-[420px] w-full object-cover object-top sm:h-[480px]"
                        />
                    </div>
                    <div class="absolute -bottom-6 -left-6 hidden rounded-2xl bg-white px-6 py-4 shadow-xl sm:block">
                        <p class="font-['Poppins'] text-2xl font-bold text-[#0B1B36]">SMEKISA</p>
                        <p class="text-xs text-[#5B6472]">Kota Blitar</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== TENTANG ===== -->
        <section id="tentang" class="relative overflow-hidden bg-white py-24">
            <div class="pointer-events-none absolute right-0 top-1/3 h-72 w-72 rounded-full bg-[#9CC3F0] opacity-20 blur-[100px]"></div>

            <div class="mx-auto grid max-w-7xl items-center gap-14 px-6 lg:grid-cols-2 lg:px-8">
                <div class="relative">
                    <img
                        src="/images/landing/saluting-girls.jpg"
                        alt="Siswi SMKS Islam 1 Kota Blitar saat upacara"
                        class="h-[420px] w-full rounded-3xl object-cover shadow-xl"
                    />
                    <img
                        src="/images/landing/crowd-caps.jpg"
                        alt="Siswa SMKS Islam 1 Kota Blitar dalam barisan"
                        class="absolute -bottom-8 -right-8 hidden h-40 w-56 rounded-2xl border-4 border-white object-cover shadow-lg sm:block"
                    />
                </div>

                <div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#3E6FD9]">
                        Profil Sekolah
                    </span>
                    <h2 class="mt-3 font-['Poppins'] text-3xl font-bold text-[#0B1B36] sm:text-4xl">
                        Tentang SMKS Islam 1 Kota Blitar
                    </h2>
                    <p class="mt-5 leading-relaxed text-[#5B6472]">
                        SMKS Islam 1 Kota Blitar (SMEKISA) mendidik siswa dengan memadukan
                        kompetensi keahlian dan nilai-nilai keislaman. Selain kegiatan belajar
                        di kelas, sekolah mendorong siswa aktif berorganisasi dan mengikuti
                        ekstrakurikuler untuk mengembangkan bakat serta karakter kepemimpinan.
                    </p>
                    <p class="mt-4 leading-relaxed text-[#5B6472]">
                        Untuk mendukung kegiatan ekstrakurikuler yang semakin banyak diikuti
                        siswa, sekolah menghadirkan aplikasi presensi digital agar kehadiran
                        setiap kegiatan tercatat rapi dan mudah dipantau oleh pelatih maupun
                        admin.
                    </p>

                    <a
                        href="https://smkislam1blitar.sch.id"
                        target="_blank"
                        class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-[#3E6FD9] hover:text-[#0B1B36]"
                    >
                        Kunjungi website sekolah
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ===== FITUR ===== -->
        <section id="fitur" class="bg-[#F4F7FC] py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#3E6FD9]">
                        Fitur Aplikasi
                    </span>
                    <h2 class="mt-3 font-['Poppins'] text-3xl font-bold text-[#0B1B36] sm:text-4xl">
                        Kenapa Presensi Digital?
                    </h2>
                    <p class="mt-4 leading-relaxed text-[#5B6472]">
                        Dirancang agar presensi ekstrakurikuler jadi lebih ringkas untuk
                        siswa, pelatih, dan admin sekolah.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="f in features"
                        :key="f.title"
                        class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-black/5 transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#0B1B36]">
                            <svg v-if="f.icon === 'clock'" class="h-6 w-6 text-[#F2A93B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else-if="f.icon === 'history'" class="h-6 w-6 text-[#F2A93B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8M3 3v5h5M12 7v5l3 3" />
                            </svg>
                            <svg v-else-if="f.icon === 'chart'" class="h-6 w-6 text-[#F2A93B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M8 17V10m5 7V6m5 11v-4" />
                            </svg>
                            <svg v-else class="h-6 w-6 text-[#F2A93B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-7.13a4 4 0 110 8 4 4 0 010-8zm7 4a4 4 0 00-2-3.46M6 9a4 4 0 012-3.46" />
                            </svg>
                        </div>
                        <h3 class="mt-5 font-['Poppins'] text-lg font-semibold text-[#0B1B36]">
                            {{ f.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-[#5B6472]">
                            {{ f.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== CTA ===== -->
        <section class="relative overflow-hidden bg-[#0B1B36] py-20">
            <div class="pointer-events-none absolute left-1/2 top-0 h-72 w-72 -translate-x-1/2 rounded-full bg-[#F2A93B] opacity-20 blur-[110px]"></div>
            <div class="relative mx-auto max-w-3xl px-6 text-center lg:px-8">
                <h2 class="font-['Poppins'] text-3xl font-bold text-white sm:text-4xl">
                    Siap Mulai Presensi Ekstrakurikuler?
                </h2>
                <p class="mt-4 text-[#C7D6EE]">
                    Masuk menggunakan akun yang telah diberikan sekolah untuk mulai
                    mencatat dan memantau kehadiran ekstrakurikulermu.
                </p>
                <Link
                    v-if="canLogin"
                    :href="$page.props.auth.user ? route('dashboard') : route('login')"
                    class="mt-8 inline-block rounded-full bg-[#F2A93B] px-8 py-3 text-sm font-semibold text-[#0B1B36] shadow-lg shadow-[#F2A93B]/20 transition hover:bg-[#f7c878]"
                >
                    {{ $page.props.auth.user ? 'Buka Dashboard' : 'Masuk Sekarang' }}
                </Link>
            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <footer class="bg-[#081428] py-12">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-8 border-b border-white/10 pb-8 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <img src="/images/landing/logo-sekolah.png" alt="Logo SMK Islam 1 Kota Blitar" class="h-12 w-12 object-contain" />
                        <div class="leading-tight">
                            <p class="font-['Poppins'] text-lg font-bold text-white">SMEKISA</p>
                            <p class="text-xs text-[#9CC3F0]">SMKS Islam 1 Kota Blitar</p>
                        </div>
                    </div>

                    <div class="flex gap-6 text-sm text-[#C7D6EE]">
                        <a href="#beranda" class="hover:text-white">Beranda</a>
                        <a href="#tentang" class="hover:text-white">Tentang</a>
                        <a href="#fitur" class="hover:text-white">Fitur</a>
                        <a href="https://smkislam1blitar.sch.id" target="_blank" class="hover:text-white">Website</a>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-between gap-4 pt-6 text-xs text-[#7C8CAB] sm:flex-row">
                    <p>&copy; {{ new Date().getFullYear() }} SMKS Islam 1 Kota Blitar. Sistem Presensi Ekstrakurikuler.</p>
                    <p>smkislam1blitar.sch.id</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap');
</style>
