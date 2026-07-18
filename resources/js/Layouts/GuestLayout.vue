<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen font-['Inter']">
        <!-- ===== LEFT PANEL — BRAND / PHOTO ===== -->
        <div class="relative hidden w-1/2 overflow-hidden bg-[#0B1B36] lg:flex lg:flex-col lg:justify-between">
            <img
                src="/images/landing/hero-mpls.jpg"
                alt="Siswa SMK Islam 1 Kota Blitar"
                class="absolute inset-0 h-full w-full object-cover object-top opacity-40"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-[#0B1B36] via-[#0B1B36]/80 to-[#0B1B36]/40"></div>

            <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#F2A93B] opacity-20 blur-[110px]"></div>

            <div class="relative z-10 flex items-center gap-3 px-12 pt-10">
                <img src="/images/landing/logo-sekolah.png" alt="Logo SMK Islam 1 Kota Blitar" class="h-12 w-12 object-contain" />
                <div class="leading-tight">
                    <p class="font-['Poppins'] text-base font-bold text-white">SMEKISA</p>
                    <p class="text-xs text-[#9CC3F0]">SMK Islam 1 Kota Blitar</p>
                </div>
            </div>

            <div class="relative z-10 px-12 pb-14">
                <span class="inline-flex items-center rounded-full border border-[#F2A93B]/40 bg-[#F2A93B]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-[#F2A93B]">
                    Sistem Presensi Ekstrakurikuler
                </span>
                <h1 class="mt-5 max-w-md font-['Poppins'] text-3xl font-bold leading-snug text-white">
                    Satu Akun untuk Semua Kegiatan Ekstrakurikulermu
                </h1>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-[#C7D6EE]">
                    Masuk untuk mencatat dan memantau kehadiran ekstrakurikuler secara
                    real-time, kapan saja.
                </p>
            </div>
        </div>

        <!-- ===== RIGHT PANEL — FORM ===== -->
        <div class="flex w-full flex-col justify-center bg-[#F4F7FC] px-6 py-12 sm:px-12 lg:w-1/2 lg:px-20">
            <div class="mx-auto w-full max-w-sm">
                <Link href="/" class="mb-10 flex items-center gap-3 lg:hidden">
                    <img src="/images/landing/logo-sekolah.png" alt="Logo SMK Islam 1 Kota Blitar" class="h-11 w-11 object-contain" />
                    <div class="leading-tight">
                        <p class="font-['Poppins'] text-sm font-bold text-[#0B1B36]">SMEKISA</p>
                        <p class="text-xs text-[#5B6472]">SMK Islam 1 Kota Blitar</p>
                    </div>
                </Link>

                <div class="rounded-3xl bg-white p-8 shadow-xl shadow-black/5 ring-1 ring-black/5 sm:p-10">
                    <slot />
                </div>

                <Link
                    href="/"
                    class="mt-6 flex items-center justify-center gap-2 text-sm font-medium text-[#5B6472] transition hover:text-[#0B1B36]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Beranda
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap');
</style>
